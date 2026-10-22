<?php

namespace App\Services;

use App\Models\MeasurementUnit;
use App\Models\QuotationItem;
use App\Models\StandardGood;
use Illuminate\Support\Collection;

class OfferComparison
{
    /**
     * Faktor pengali dari satuan ke satuan dasar barang standar.
     * Null jika satuan tidak bisa dikonversi dengan aman (mis. kg ke pcs).
     */
    public static function factorToBase(
        MeasurementUnit $unit,
        int $baseUnitId
    ): ?float {
        if ((int) $unit->id === $baseUnitId) {
            return 1.0;
        }

        if ((int) $unit->base_unit_id === $baseUnitId) {
            $factor = (float) $unit->conversion_factor;

            return $factor > 0 ? $factor : null;
        }

        return null;
    }

    public static function isConvertible(
        MeasurementUnit $unit,
        StandardGood $good
    ): bool {
        return self::factorToBase($unit, (int) $good->base_unit_id) !== null;
    }

    /**
     * Harga setelah PPN per satuan dasar barang standar.
     *
     * Contoh: Rp 120.000 per dus (belum PPN 11%), 1 dus = 12 pcs
     * => 120.000 x 1,11 / 12 = Rp 11.100 per pcs.
     */
    public static function normalizedUnitPrice(QuotationItem $item): ?float
    {
        $good = $item->standardGood;
        $unit = $item->unit;
        $quotation = $item->quotation;

        if (!$good || !$unit || !$quotation) {
            return null;
        }

        $factor = self::factorToBase($unit, (int) $good->base_unit_id);
        $quantity = (float) $item->quantity_in_base_unit;

        if ($factor === null || $quantity <= 0) {
            return null;
        }

        $price = (float) $item->quoted_price;

        if ($quotation->vat_type === 'excluded') {
            $price *= 1 + ((float) $quotation->vat_rate / 100);
        }

        return $price / ($quantity * $factor);
    }

    /**
     * Kelompokkan item per barang standar, urut dari harga termurah.
     *
     * @param  Collection<int, QuotationItem>  $items
     */
    public static function group(Collection $items): Collection
    {
        return $items
            ->map(function (QuotationItem $item) {
                $price = self::normalizedUnitPrice($item);

                if ($price === null || !$item->standardGood->baseUnit) {
                    return null;
                }

                return [
                    'good' => $item->standardGood,
                    'base_unit' => $item->standardGood->baseUnit,
                    'quotation' => $item->quotation,
                    'item' => $item,
                    'unit_price' => $price,
                ];
            })
            ->filter()
            ->groupBy(fn ($row) => $row['good']->id)
            ->map(function ($rows) {
                $offers = $rows->sortBy('unit_price')->values();

                return [
                    'good' => $offers->first()['good'],
                    'base_unit' => $offers->first()['base_unit'],
                    'offers' => $offers,
                ];
            })
            ->sortBy(fn ($group) => $group['good']->name)
            ->values();
    }
}
