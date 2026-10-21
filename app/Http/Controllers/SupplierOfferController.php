<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Supplier;
use App\Models\SupplierSalesContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SupplierOfferController extends Controller
{
    public function create()
    {
        $goods = DB::table('standard_goods')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $units = DB::table('measurement_units')
            ->orderBy('name')
            ->get();

        return view('supplier-offer', compact('goods', 'units'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:2000'],

            'sales_name' => ['required', 'string', 'max:255'],
            'sales_email' => ['required', 'email', 'max:255'],
            'sales_phone' => ['nullable', 'string', 'max:50'],

            'standard_good_id' => [
                'nullable', 'integer', 'exists:standard_goods,id',
            ],
            'supplier_item_name' => ['required', 'string', 'max:255'],
            'supplier_item_code' => ['nullable', 'string', 'max:100'],
            'supplier_unit_text' => ['required', 'string', 'max:100'],
            'unit_id' => [
                'nullable', 'integer', 'exists:measurement_units,id',
            ],
            'quoted_price' => ['required', 'numeric', 'min:0.01'],
            'minimum_order_quantity' => [
                'required', 'numeric', 'min:0.0001',
            ],
            'vat_type' => ['required', 'in:included,excluded'],
            'vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $token = (string) Str::uuid();

        DB::transaction(function () use ($data, $token) {
            $normalizedName = Str::lower(
                Str::squish($data['company_name'])
            );

            $supplier = Supplier::where(
                'normalized_company_name',
                $normalizedName
            )->first();

            if (!$supplier) {
                $supplier = Supplier::create([
                    'company_name' => $data['company_name'],
                    'normalized_company_name' => $normalizedName,
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'address' => $data['address'] ?? null,
                ]);
            } else {
                $supplier->update([
                    'company_name' => $data['company_name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'address' => $data['address'] ?? null,
                ]);
            }

            $contact = SupplierSalesContact::create([
                'supplier_id' => $supplier->id,
                'name' => $data['sales_name'],
                'email' => $data['sales_email'],
                'phone' => $data['sales_phone'] ?? null,
                'is_primary' => false,
            ]);

            $quotation = Quotation::create([
                'public_token' => $token,
                'supplier_id' => $supplier->id,
                'sales_contact_id' => $contact->id,
                'sales_name' => $data['sales_name'],
                'sales_email' => $data['sales_email'],
                'sales_phone' => $data['sales_phone'] ?? null,
                'currency' => 'IDR',
                'vat_type' => $data['vat_type'],
                'vat_rate' => $data['vat_rate'] ?? 0,
                'valid_from' => now()->toDateString(),
                'valid_until' => $data['valid_until'] ?? null,
                'submitted_at' => now(),
                'status' => 'submitted',
                'notes' => $data['notes'] ?? null,
            ]);

            QuotationItem::create([
                'quotation_id' => $quotation->id,
                'standard_good_id' => $data['standard_good_id'] ?? null,
                'supplier_item_name' => $data['supplier_item_name'],
                'supplier_item_code' => $data['supplier_item_code'] ?? null,
                'supplier_unit_text' => $data['supplier_unit_text'],
                'unit_id' => $data['unit_id'] ?? null,
                'quantity_in_base_unit' => 1,
                'quoted_price' => $data['quoted_price'],
                'minimum_order_quantity' => $data['minimum_order_quantity'],
                'notes' => $data['notes'] ?? null,
            ]);
        });

        return redirect()->route('supplier-offer.receipt', [
            'token' => $token,
        ]);
    }

    public function receipt(string $token)
    {
        $quotation = Quotation::with('supplier')
            ->where('public_token', $token)
            ->firstOrFail();

        return view('supplier-offer-receipt', compact('quotation'));
    }
}
