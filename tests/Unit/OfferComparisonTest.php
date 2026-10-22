<?php

namespace Tests\Unit;

use App\Models\MeasurementUnit;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\StandardGood;
use App\Services\OfferComparison;
use PHPUnit\Framework\TestCase;

class OfferComparisonTest extends TestCase
{
    private MeasurementUnit $pcs;
    private MeasurementUnit $kg;
    private MeasurementUnit $gram;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pcs = $this->unit(1, 'pcs');
        $this->kg = $this->unit(2, 'kg');
        $this->gram = $this->unit(3, 'g', baseUnitId: 2, factor: 0.001);
    }

    public function test_price_is_divided_by_pack_size(): void
    {
        // Rp 120.000 per dus isi 12 pcs, sudah termasuk PPN.
        $item = $this->item($this->pcs, $this->pcs, 120000, 12, 'included');

        $this->assertEqualsWithDelta(
            10000,
            OfferComparison::normalizedUnitPrice($item),
            0.001
        );
    }

    public function test_vat_is_added_when_price_excludes_vat(): void
    {
        $item = $this->item($this->pcs, $this->pcs, 10000, 1, 'excluded', 11);

        $this->assertEqualsWithDelta(
            11100,
            OfferComparison::normalizedUnitPrice($item),
            0.001
        );
    }

    public function test_vat_is_not_added_twice_when_price_includes_vat(): void
    {
        $item = $this->item($this->pcs, $this->pcs, 11100, 1, 'included', 11);

        $this->assertEqualsWithDelta(
            11100,
            OfferComparison::normalizedUnitPrice($item),
            0.001
        );
    }

    public function test_derived_unit_is_converted_to_base_unit(): void
    {
        // Rp 25.000 per sak 500 gram = Rp 50.000 per kg.
        $item = $this->item($this->kg, $this->gram, 25000, 500, 'included');

        $this->assertEqualsWithDelta(
            50000,
            OfferComparison::normalizedUnitPrice($item),
            0.001
        );
    }

    public function test_incompatible_unit_cannot_be_compared(): void
    {
        $item = $this->item($this->pcs, $this->kg, 10000, 1, 'included');

        $this->assertNull(OfferComparison::normalizedUnitPrice($item));
        $this->assertFalse(
            OfferComparison::isConvertible($this->kg, $item->standardGood)
        );
    }

    public function test_group_sorts_offers_from_cheapest_normalized_price(): void
    {
        // Harga per dus lebih mahal, tapi per pcs lebih murah.
        $box = $this->item($this->pcs, $this->pcs, 100000, 12, 'included');
        $loose = $this->item($this->pcs, $this->pcs, 9000, 1, 'included');
        $invalid = $this->item($this->pcs, $this->kg, 1, 1, 'included');

        $groups = OfferComparison::group(collect([$loose, $box, $invalid]));

        $this->assertCount(1, $groups);
        $this->assertCount(2, $groups[0]['offers']);
        $this->assertSame($box, $groups[0]['offers'][0]['item']);
        $this->assertSame($loose, $groups[0]['offers'][1]['item']);
    }

    private function unit(
        int $id,
        string $code,
        ?int $baseUnitId = null,
        float $factor = 1
    ): MeasurementUnit {
        $unit = new MeasurementUnit([
            'code' => $code,
            'name' => $code,
            'base_unit_id' => $baseUnitId,
            'conversion_factor' => $factor,
        ]);
        $unit->id = $id;

        return $unit;
    }

    private function item(
        MeasurementUnit $goodBaseUnit,
        MeasurementUnit $unit,
        float $price,
        float $quantity,
        string $vatType,
        float $vatRate = 11
    ): QuotationItem {
        $good = new StandardGood(['name' => 'Barang', 'base_unit_id' => $goodBaseUnit->id]);
        $good->id = $goodBaseUnit->id;
        $good->setRelation('baseUnit', $goodBaseUnit);

        $quotation = new Quotation(['vat_type' => $vatType, 'vat_rate' => $vatRate]);

        $item = new QuotationItem([
            'quoted_price' => $price,
            'quantity_in_base_unit' => $quantity,
        ]);
        $item->setRelation('standardGood', $good);
        $item->setRelation('unit', $unit);
        $item->setRelation('quotation', $quotation);

        return $item;
    }
}
