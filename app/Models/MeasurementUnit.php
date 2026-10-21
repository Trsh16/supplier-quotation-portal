<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeasurementUnit extends Model
{
    protected $fillable = [
        'code',
        'name',
        'dimension',
        'base_unit_id',
        'conversion_factor',
    ];

    protected function casts(): array
    {
        return [
            'conversion_factor' => 'decimal:6',
        ];
    }

    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'base_unit_id'
        );
    }

    public function derivedUnits(): HasMany
    {
        return $this->hasMany(
            self::class,
            'base_unit_id'
        );
    }

    public function standardGoods(): HasMany
    {
        return $this->hasMany(
            StandardGood::class,
            'base_unit_id'
        );
    }
}
