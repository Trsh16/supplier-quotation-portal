<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_token',
        'supplier_id',
        'sales_contact_id',
        'sales_name',
        'sales_email',
        'sales_phone',
        'currency',
        'vat_type',
        'vat_rate',
        'valid_from',
        'valid_until',
        'submitted_at',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'valid_from' => 'date',
            'valid_until' => 'date',
            'submitted_at' => 'datetime',
            'vat_rate' => 'decimal:2',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function salesContact(): BelongsTo
    {
        return $this->belongsTo(
            SupplierSalesContact::class,
            'sales_contact_id'
        );
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(QuotationDocument::class);
    }
}
