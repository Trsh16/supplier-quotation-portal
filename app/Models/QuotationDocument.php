<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationDocument extends Model
{
    protected $fillable = [
        'quotation_id',
        'original_filename',
        'storage_path',
        'mime_type',
        'size_bytes',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }
}
