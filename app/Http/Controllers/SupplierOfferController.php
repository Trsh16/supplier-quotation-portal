<?php

namespace App\Http\Controllers;

use App\Models\MeasurementUnit;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Supplier;
use App\Models\StandardGood;
use App\Models\SupplierSalesContact;
use App\Services\OfferComparison;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

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
                'required',
                'integer',
                'exists:standard_goods,id',
            ],
            'supplier_item_name' => ['required', 'string', 'max:255'],
            'supplier_item_code' => ['nullable', 'string', 'max:100'],
            'supplier_unit_text' => ['required', 'string', 'max:100'],
            'unit_id' => [
                'required',
                'integer',
                'exists:measurement_units,id',
            ],
            'quantity_in_base_unit' => [
                'required',
                'numeric',
                'min:0.000001',
            ],
            'quoted_price' => ['required', 'numeric', 'min:0.01'],
            'minimum_order_quantity' => [
                'required',
                'numeric',
                'min:0.0001',
            ],
            'vat_type' => ['required', 'in:included,excluded'],
            'vat_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'valid_until' => ['required', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:2000'],

            // Honeypot: field tersembunyi yang hanya diisi oleh bot.
            'website' => ['prohibited'],
        ]);

        self::ensureUnitMatchesGood($data);

        $token = (string) Str::uuid();

        // Simpan semua data penawaran dalam satu transaksi database.
        $quotation = DB::transaction(function () use ($data, $token) {
            $normalizedName = Supplier::normalizeName($data['company_name']);

            $supplier = Supplier::firstOrCreate(
                ['normalized_company_name' => $normalizedName],
                [
                    'company_name' => Str::squish($data['company_name']),
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'address' => $data['address'] ?? null,
                ]
            );

            // Form ini publik: data master supplier yang sudah ada tidak
            // ditimpa, hanya field yang masih kosong yang dilengkapi.
            if (!$supplier->wasRecentlyCreated) {
                $supplier->fill(array_filter([
                    'email' => $supplier->email ? null : $data['email'],
                    'phone' => $supplier->phone ? null : ($data['phone'] ?? null),
                    'address' => $supplier->address ? null : ($data['address'] ?? null),
                ]))->save();
            }

            // Sales yang sama (email sama) dipakai ulang; sales baru ditambahkan.
            $contact = SupplierSalesContact::firstOrNew([
                'supplier_id' => $supplier->id,
                'email' => Str::lower($data['sales_email']),
            ]);
            if (!$contact->exists) {
                // Sales pertama dari supplier otomatis jadi kontak utama.
                $contact->is_primary = !$supplier->salesContacts()->exists();
            }

            $contact->fill([
                'name' => $data['sales_name'],
                'phone' => $data['sales_phone'] ?? $contact->phone,
            ])->save();

            $quotation = Quotation::create([
                'public_token' => $token,
                'supplier_id' => $supplier->id,
                'sales_contact_id' => $contact->id,
                'sales_name' => $data['sales_name'],
                'sales_email' => $data['sales_email'],
                'sales_phone' => $data['sales_phone'] ?? null,
                'currency' => 'IDR',
                'vat_type' => $data['vat_type'],
                'vat_rate' => $data['vat_rate'],
                'valid_from' => now()->toDateString(),
                'valid_until' => $data['valid_until'],
                'submitted_at' => now(),
                'status' => 'submitted',
                'notes' => $data['notes'] ?? null,
            ]);

            QuotationItem::create([
                'quotation_id' => $quotation->id,
                'standard_good_id' => $data['standard_good_id'],
                'supplier_item_name' => $data['supplier_item_name'],
                'supplier_item_code' => $data['supplier_item_code'] ?? null,
                'supplier_unit_text' => $data['supplier_unit_text'],
                'unit_id' => $data['unit_id'],
                'quantity_in_base_unit' => $data['quantity_in_base_unit'],
                'quoted_price' => $data['quoted_price'],
                'minimum_order_quantity' => $data['minimum_order_quantity'],
                'notes' => $data['notes'] ?? null,
            ]);

            return $quotation;
        });

        // Simulasi email berjalan setelah transaksi berhasil disimpan.
        // MAIL_MAILER=log akan menulis email ke storage/logs/laravel.log.
        try {
            $receiptUrl = route('supplier-offer.receipt', [
                'token' => $quotation->public_token,
            ]);

            Mail::raw(
                "Halo {$data['sales_name']},\n\n"
                . "Penawaran Anda telah berhasil kami terima.\n\n"
                . "Perusahaan: {$data['company_name']}\n"
                . "Nomor referensi: {$quotation->public_token}\n"
                . "Bukti penerimaan: {$receiptUrl}\n\n"
                . "Email ini merupakan konfirmasi otomatis dari sistem "
                . "Portal Penawaran Supplier.",
                function ($message) use ($data) {
                    $message
                        ->to($data['sales_email'])
                        ->subject(
                            'Konfirmasi Penerimaan Penawaran Supplier'
                        );
                }
            );

            Log::info('Simulasi email konfirmasi penawaran diproses.', [
                'quotation_id' => $quotation->id,
                'recipient' => $data['sales_email'],
            ]);
        } catch (Throwable $e) {
            // Email gagal tidak membatalkan penawaran yang sudah tersimpan.
            Log::warning('Simulasi email konfirmasi gagal.', [
                'quotation_id' => $quotation->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('supplier-offer.receipt', [
            'token' => $token,
        ]);
    }

    /**
     * Tolak satuan yang tidak bisa dikonversi ke satuan dasar barang,
     * agar setiap penawaran pasti bisa ikut dibandingkan.
     */
    public static function ensureUnitMatchesGood(array $data): void
    {
        $good = StandardGood::with('baseUnit')
            ->findOrFail($data['standard_good_id']);
        $unit = MeasurementUnit::findOrFail($data['unit_id']);

        if (!OfferComparison::isConvertible($unit, $good)) {
            throw ValidationException::withMessages([
                'unit_id' => "Satuan {$unit->name} tidak dapat dikonversi ke "
                    . "satuan dasar {$good->name} ({$good->baseUnit->name}).",
            ]);
        }
    }

    public function receipt(string $token)
    {
        $quotation = Quotation::with('supplier')
            ->where('public_token', $token)
            ->firstOrFail();

        return view('supplier-offer-receipt', compact('quotation'));
    }
}
