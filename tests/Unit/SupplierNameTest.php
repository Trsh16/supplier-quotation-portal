<?php

namespace Tests\Unit;

use App\Models\Supplier;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SupplierNameTest extends TestCase
{
    #[DataProvider('sameCompanyNames')]
    public function test_variants_of_a_company_name_normalize_to_the_same_key(
        string $name
    ): void {
        $this->assertSame('maju jaya', Supplier::normalizeName($name));
    }

    public static function sameCompanyNames(): array
    {
        return [
            ['PT Maju Jaya'],
            ['PT. Maju Jaya'],
            ['  pt   maju  jaya '],
            ['Maju Jaya, Tbk'],
            ['CV. MAJU JAYA'],
        ];
    }

    public function test_different_companies_stay_different(): void
    {
        $this->assertNotSame(
            Supplier::normalizeName('PT Maju Jaya'),
            Supplier::normalizeName('PT Maju Jaya Abadi')
        );
    }
}
