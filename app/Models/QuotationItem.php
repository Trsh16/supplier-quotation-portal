<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    protected $fillable = [
        'quotation_id',
        'standard_good_id',
        'supplier_item_name',
        'supplier_item_code',
        'supplier_unit_text',
        'unit_id',
        'quantity_in_base_unit',
        'quoted_price',
        'minimum_order_quantity',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_in_base_unit' => 'decimal:6',
            'quoted_price' => 'decimal:2',
            'minimum_order_quantity' => 'decimal:4',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function standardGood(): BelongsTo
    {
        return $this->belongsTo(StandardGood::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(
            MeasurementUnit::class,
            'unit_id'
        );
    }
}

