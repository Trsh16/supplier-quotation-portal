<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\QuotationItem;
use Illuminate\Http\Request;

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
}