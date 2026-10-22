<?php

namespace Tests\Feature;

use App\Models\MeasurementUnit;
use App\Models\Quotation;
use App\Models\StandardGood;
use App\Models\Supplier;
use App\Models\SupplierSalesContact;
use Database\Seeders\SupplierQuotationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierOfferSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SupplierQuotationSeeder::class);
    }

    public function test_public_form_is_accessible_without_login(): void
    {
        $this->get('/supplier-offer')
            ->assertOk()
            ->assertSee('A4 Paper');
    }

    public function test_valid_offer_is_stored_and_redirects_to_receipt(): void
    {
        $response = $this->post('/supplier-offer', $this->payload());

        $quotation = Quotation::with('items', 'supplier')->sole();

        $response->assertRedirect(
            route('supplier-offer.receipt', $quotation->public_token)
        );

        $this->assertSame('submitted', $quotation->status);
        $this->assertSame('PT Maju Jaya', $quotation->supplier->company_name);
        $this->assertEquals(12, $quotation->items->first()->quantity_in_base_unit);

        $this->get(route('supplier-offer.receipt', $quotation->public_token))
            ->assertOk()
            ->assertSee($quotation->public_token);
    }

    public function test_returning_supplier_is_matched_and_new_sales_is_added(): void
    {
        $this->post('/supplier-offer', $this->payload());

        $this->post('/supplier-offer', $this->payload([
            'company_name' => 'PT. MAJU JAYA',
            'email' => 'penipu@example.com',
            'sales_name' => 'Sales Baru',
            'sales_email' => 'baru@majujaya.test',
        ]));

        $this->assertSame(1, Supplier::count());
        $this->assertSame(2, Quotation::count());
        $this->assertSame(2, SupplierSalesContact::count());

        // Data master tidak boleh ditimpa dari form publik.
        $this->assertSame('info@majujaya.test', Supplier::sole()->email);

        $contacts = SupplierSalesContact::orderBy('id')->get();
        $this->assertTrue($contacts[0]->is_primary);
        $this->assertFalse($contacts[1]->is_primary);
    }

    public function test_same_sales_returning_reuses_contact(): void
    {
        $this->post('/supplier-offer', $this->payload());
        $this->post('/supplier-offer', $this->payload([
            'sales_email' => 'BUDI@majujaya.test',
        ]));

        $this->assertSame(1, SupplierSalesContact::count());
        $this->assertSame(2, Quotation::count());
    }

    public function test_unit_that_cannot_be_converted_is_rejected(): void
    {
        // A4 Paper memakai satuan dasar pcs; kg tidak bisa dikonversi.
        $this->post('/supplier-offer', $this->payload([
            'unit_id' => MeasurementUnit::where('code', 'kg')->value('id'),
        ]))->assertSessionHasErrors('unit_id');

        $this->assertSame(0, Quotation::count());
    }

    public function test_expired_validity_date_is_rejected(): void
    {
        $this->post('/supplier-offer', $this->payload([
            'valid_until' => now()->subDay()->toDateString(),
        ]))->assertSessionHasErrors('valid_until');
    }

    public function test_honeypot_blocks_bot_submission(): void
    {
        $this->post('/supplier-offer', $this->payload([
            'website' => 'http://spam.test',
        ]))->assertSessionHasErrors('website');

        $this->assertSame(0, Quotation::count());
    }

    public function test_public_form_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/supplier-offer', $this->payload());
        }

        $this->post('/supplier-offer', $this->payload())->assertStatus(429);
    }

    public function test_unknown_receipt_token_returns_404(): void
    {
        $this->get('/supplier-offer/receipt/not-a-real-token')->assertNotFound();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'company_name' => 'PT Maju Jaya',
            'email' => 'info@majujaya.test',
            'phone' => '021123456',
            'address' => 'Jakarta',
            'sales_name' => 'Budi',
            'sales_email' => 'budi@majujaya.test',
            'sales_phone' => '0812345678',
            'standard_good_id' => StandardGood::where('sku', 'OFF-001')->value('id'),
            'supplier_item_name' => 'Kertas HVS A4 80gsm',
            'supplier_item_code' => 'HVS-A4',
            'supplier_unit_text' => 'dus',
            'quantity_in_base_unit' => 12,
            'unit_id' => MeasurementUnit::where('code', 'pcs')->value('id'),
            'quoted_price' => 120000,
            'minimum_order_quantity' => 1,
            'vat_type' => 'excluded',
            'vat_rate' => 11,
            'valid_until' => now()->addMonth()->toDateString(),
        ], $overrides);
    }
}
