<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\QuotationItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProcurementDashboardController extends Controller
{
    public function index(Request $request)
    {
        $stats = [
            'total' => Quotation::count(),
            'submitted' => Quotation::where('status', 'submitted')->count(),
            'under_review' => Quotation::where('status', 'under_review')->count(),
            'accepted' => Quotation::where('status', 'accepted')->count(),
        ];

        $search = trim((string) $request->query('search', ''));

        $query = Quotation::with([
            'supplier',
            'items.standardGood',
            'items.unit',
        ])->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('public_token', 'like', "%{$search}%")
                    ->orWhere('sales_name', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($sq) use ($search) {
                        $sq->where('company_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('items', function ($iq) use ($search) {
                        $iq->where('supplier_item_name', 'like', "%{$search}%");
                    });
            });
        }

        $quotations = $query->paginate(10)->withQueryString();

        // Ambil penawaran aktif yang sudah dikirim dan punya barang standar.
        $items = QuotationItem::with([
            'standardGood.baseUnit',
            'unit',
            'quotation.supplier',
        ])
            ->whereNotNull('standard_good_id')
            ->whereHas('quotation', function ($q) {
                $q->whereIn('status', [
                    'submitted',
                    'under_review',
                    'accepted',
                ])->where(function ($dateQuery) {
                    $dateQuery->whereNull('valid_until')
                        ->orWhereDate('valid_until', '>=', today());
                });
            })
            ->get();

        // Harga dihitung setelah PPN, lalu dinormalisasi ke satuan dasar.
        $comparisonRows = $items->map(function ($item) {
            $good = $item->standardGood;
            $unit = $item->unit;

            if (!$good || !$unit || !$good->baseUnit) {
                return null;
            }

            $baseUnitId = (int) $good->base_unit_id;

            if ((int) $unit->id === $baseUnitId) {
                $factor = 1;
            } elseif ((int) $unit->base_unit_id === $baseUnitId) {
                $factor = (float) $unit->conversion_factor;
            } else {
                // Satuan tidak bisa dikonversi dengan aman.
                return null;
            }

            if ($factor <= 0) {
                return null;
            }

            $quotation = $item->quotation;
            $price = (float) $item->quoted_price;

            if ($quotation->vat_type === 'excluded') {
                $price *= 1 + ((float) $quotation->vat_rate / 100);
            }

            return [
                'good' => $good,
                'base_unit' => $good->baseUnit,
                'quotation' => $quotation,
                'item' => $item,
                'unit_price' => $price / $factor,
            ];
        })->filter()->values();

        $comparisonGroups = $comparisonRows
            ->groupBy(fn ($row) => $row['good']->id)
            ->map(function ($rows) {
                $offers = $rows->sortBy('unit_price')->values();

                return [
                    'good' => $offers->first()['good'],
                    'base_unit' => $offers->first()['base_unit'],
                    'offers' => $offers,
                ];
            })->values();

        return view('procurement-dashboard', compact(
            'stats',
            'quotations',
            'search',
            'comparisonGroups'
        ));
    }

public function review(Quotation $quotation)
{
    if ($quotation->status !== 'submitted') {
        return redirect()
            ->route('procurement.dashboard')
            ->with('success', 'Status penawaran ini bukan Penawaran Masuk.');
    }

    $quotation->update([
        'status' => 'under_review',
    ]);

    return redirect()
        ->route('procurement.dashboard')
        ->with('success', 'Penawaran berhasil ditandai Sedang Ditinjau.');
}

public function accept(Quotation $quotation)
{
    if (!in_array($quotation->status, ['submitted', 'under_review'], true)) {
        return redirect()
            ->route('procurement.dashboard')
            ->with('success', 'Penawaran ini tidak dapat diterima dari status saat ini.');
    }

    $quotation->update([
        'status' => 'accepted',
    ]);

    return redirect()
        ->route('procurement.dashboard')
        ->with('success', 'Penawaran berhasil diterima.');
}

public function edit(Quotation $quotation)
{
    $quotation->load('items');

    $item = $quotation->items->first();

    abort_if(!$item, 404, 'Barang pada penawaran ini tidak ditemukan.');

    $goods = DB::table('standard_goods')
        ->where('is_active', true)
        ->orderBy('name')
        ->get();

    $units = DB::table('measurement_units')
        ->orderBy('name')
        ->get();

    return view('quotation-edit', compact(
        'quotation',
        'item',
        'goods',
        'units'
    ));
}

public function update(Request $request, Quotation $quotation)
{
    $data = $request->validate([
        'standard_good_id' => [
            'required', 'integer', 'exists:standard_goods,id',
        ],
        'supplier_item_name' => [
            'required', 'string', 'max:255',
        ],
        'supplier_item_code' => [
            'nullable', 'string', 'max:100',
        ],
        'supplier_unit_text' => [
            'required', 'string', 'max:100',
        ],
        'unit_id' => [
            'required', 'integer', 'exists:measurement_units,id',
        ],
        'quoted_price' => [
            'required', 'numeric', 'min:0.01',
        ],
        'minimum_order_quantity' => [
            'required', 'numeric', 'min:0.0001',
        ],
        'vat_type' => [
            'required', 'in:included,excluded',
        ],
        'vat_rate' => [
            'required', 'numeric', 'min:0', 'max:100',
        ],
        'valid_until' => [
            'nullable', 'date', 'after_or_equal:today',
        ],
        'notes' => ['nullable', 'string', 'max:2000'],
    ]);

    DB::transaction(function () use ($quotation, $data) {
        $quotation->update([
            'vat_type' => $data['vat_type'],
            'vat_rate' => $data['vat_rate'],
            'valid_until' => $data['valid_until'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        $item = $quotation->items()->firstOrFail();

        $item->update([
            'standard_good_id' => $data['standard_good_id'],
            'supplier_item_name' => $data['supplier_item_name'],
            'supplier_item_code' => $data['supplier_item_code'] ?? null,
            'supplier_unit_text' => $data['supplier_unit_text'],
            'unit_id' => $data['unit_id'],
            'quoted_price' => $data['quoted_price'],
            'minimum_order_quantity' => $data['minimum_order_quantity'],
            'notes' => $data['notes'] ?? null,
        ]);
    });

    return redirect()
        ->route('procurement.dashboard')
        ->with('success', 'Penawaran berhasil diperbarui.');
}

    public function destroy(\App\Models\Quotation $quotation)
{
    DB::transaction(function () use ($quotation) {
        $quotation->documents()->delete();
        $quotation->items()->delete();
        $quotation->delete();
    });

    return redirect()
        ->route('procurement.dashboard')
        ->with('success', 'Penawaran berhasil dihapus.');
}
}