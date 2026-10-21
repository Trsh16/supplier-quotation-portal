<?php

namespace Database\Seeders;

use App\Models\MeasurementUnit;
use App\Models\StandardGood;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierQuotationSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $units = [
                ['code' => 'pcs', 'name' => 'Pieces', 'dimension' => 'count'],
                ['code' => 'box', 'name' => 'Box', 'dimension' => 'count'],
                ['code' => 'pack', 'name' => 'Pack', 'dimension' => 'count'],
                ['code' => 'kg', 'name' => 'Kilogram', 'dimension' => 'mass'],
                ['code' => 'g', 'name' => 'Gram', 'dimension' => 'mass'],
                ['code' => 'liter', 'name' => 'Liter', 'dimension' => 'volume'],
                ['code' => 'ml', 'name' => 'Milliliter', 'dimension' => 'volume'],
            ];

            foreach ($units as $unit) {
                MeasurementUnit::updateOrCreate(
                    ['code' => $unit['code']],
                    [
                        'name' => $unit['name'],
                        'dimension' => $unit['dimension'],
                        'base_unit_id' => null,
                        'conversion_factor' => 1,
                    ]
                );
            }

            $baseUnits = MeasurementUnit::whereIn(
                'code',
                ['pcs', 'kg', 'liter']
            )->get()->keyBy('code');

            foreach ([
                'g' => ['base' => 'kg', 'factor' => 0.001],
                'ml' => ['base' => 'liter', 'factor' => 0.001],
            ] as $code => $conversion) {
                MeasurementUnit::where('code', $code)->update([
                    'base_unit_id' => $baseUnits[$conversion['base']]->id,
                    'conversion_factor' => $conversion['factor'],
                ]);
            }

            $goods = [
                [
                    'sku' => 'OFF-001',
                    'name' => 'A4 Paper',
                    'description' => 'Standard A4 office paper',
                    'base_unit' => 'pcs',
                ],
                [
                    'sku' => 'CLN-001',
                    'name' => 'Cleaning Liquid',
                    'description' => 'General-purpose cleaning liquid',
                    'base_unit' => 'liter',
                ],
                [
                    'sku' => 'SAF-001',
                    'name' => 'Safety Gloves',
                    'description' => 'Protective work gloves',
                    'base_unit' => 'pcs',
                ],
                [
                    'sku' => 'CHE-001',
                    'name' => 'Industrial Chemical',
                    'description' => 'Industrial-use chemical material',
                    'base_unit' => 'kg',
                ],
            ];

            foreach ($goods as $good) {
                StandardGood::updateOrCreate(
                    ['sku' => $good['sku']],
                    [
                        'name' => $good['name'],
                        'description' => $good['description'],
                        'base_unit_id' => $baseUnits[$good['base_unit']]->id,
                        'is_active' => true,
                    ]
                );
            }
        });
    }
}
