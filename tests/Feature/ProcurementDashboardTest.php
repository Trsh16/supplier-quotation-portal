<?php

namespace Tests\Feature;

use App\Models\MeasurementUnit;
use App\Models\Quotation;
use App\Models\StandardGood;
use App\Models\Supplier;
use Database\Seeders\SupplierQuotationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProcurementDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'procurement.username' => 'procurement',
            'procurement.password' => 'secret',
        ]);

        $this->seed(SupplierQuotationSeeder::class);
    }

    public function test_dashboard_requires_credentials(): void
    {
        $this->get('/')->assertUnauthorized();

        $this->withBasicAuth('procurement', 'wrong')
            ->get('/')
            ->assertUnauthorized();

        $this->asProcurement()->get('/')->assertOk();
    }

    public function test_dashboard_is_closed_when_credentials_are_not_configured(): void
    {
        config(['procurement.password' => '']);

        $this->withBasicAuth('procurement', '')->get('/')->assertStatus(503);
    }

    public function test_internal_api_requires_credentials(): void
    {
        $this->getJson('/api/suppliers')->assertUnauthorized();
        $this->postJson('/api/suppliers', ['company_name' => 'X'])
            ->assertUnauthorized();

        $this->asProcurement()->getJson('/api/suppliers')->assertOk();
    }

    public function test_comparison_ranks_by_normalized_price_and_skips_expired(): void
    {
        $paper = StandardGood::where('sku', 'OFF-001')->sole();

        // Rp 100.000 / dus 12 pcs (termasuk PPN) = Rp 8.333 per pcs.
        $box = $this->offer('Supplier Dus', $paper, 100000, 12, 'included');
        // Rp 9.000 / pcs belum PPN 11% = Rp 9.990 per pcs.
        $loose = $this->offer('Supplier Ecer', $paper, 9000, 1, 'excluded');
        // Paling murah, tetapi sudah kedaluwarsa.
        $this->offer('Supplier Lama', $paper, 1000, 1, 'included', expired: true);

        $groups = $this->asProcurement()
            ->get('/')
            ->assertOk()
            ->viewData('comparisonGroups');

        $offers = $groups->firstWhere('good.id', $paper->id)['offers'];

        $this->assertCount(2, $offers);
        $this->assertTrue($offers[0]['quotation']->is($box));
        $this->assertTrue($offers[1]['quotation']->is($loose));
        $this->assertEqualsWithDelta(8333.33, $offers[0]['unit_price'], 0.01);
        $this->assertEqualsWithDelta(9990, $offers[1]['unit_price'], 0.01);
    }

    public function test_quotation_status_follows_review_then_accept(): void
    {
        $paper = StandardGood::where('sku', 'OFF-001')->sole();
        $quotation = $this->offer('Supplier A', $paper, 1000, 1, 'included');

        $this->asProcurement()->patch(route('quotations.review', $quotation));
        $this->assertSame('under_review', $quotation->fresh()->status);

        $this->asProcurement()->patch(route('quotations.accept', $quotation));
        $this->assertSame('accepted', $quotation->fresh()->status);

        // Penawaran yang sudah diterima tidak bisa ditinjau ulang.
        $this->asProcurement()->patch(route('quotations.review', $quotation));
        $this->assertSame('accepted', $quotation->fresh()->status);
    }

    private function asProcurement(): static
    {
        return $this->withBasicAuth('procurement', 'secret');
    }

    private function offer(
        string $company,
        StandardGood $good,
        float $price,
        float $quantity,
        string $vatType,
        bool $expired = false
    ): Quotation {
        $supplier = Supplier::create([
            'company_name' => $company,
            'normalized_company_name' => Supplier::normalizeName($company),
        ]);

        $quotation = Quotation::create([
            'public_token' => (string) Str::uuid(),
            'supplier_id' => $supplier->id,
            'sales_name' => 'Sales',
            'vat_type' => $vatType,
            'vat_rate' => 11,
            'valid_until' => $expired ? now()->subDay() : now()->addMonth(),
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);

        $quotation->items()->create([
            'standard_good_id' => $good->id,
            'supplier_item_name' => "{$company} item",
            'supplier_unit_text' => $quantity > 1 ? 'dus' : 'pcs',
            'unit_id' => MeasurementUnit::where('code', 'pcs')->value('id'),
            'quantity_in_base_unit' => $quantity,
            'quoted_price' => $price,
        ]);

        return $quotation;
    }
}
