<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'normalized_company_name',
        'email',
        'phone',
        'address',
    ];

    /**
     * Kunci pencocokan supplier: "PT. Maju Jaya, Tbk" dan "maju jaya"
     * dianggap perusahaan yang sama.
     */
    public static function normalizeName(string $name): string
    {
        $name = Str::lower(Str::ascii($name));
        $name = preg_replace('/[^a-z0-9]+/', ' ', $name);
        $name = preg_replace(
            '/\b(pt|cv|ud|tbk|persero|fa|koperasi)\b/',
            ' ',
            $name
        );

        return Str::squish($name);
    }

    public function salesContacts(): HasMany
    {
        return $this->hasMany(SupplierSalesContact::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }
}
