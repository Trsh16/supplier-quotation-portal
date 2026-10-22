<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUpTraits()
    {
        // RefreshDatabase menghapus semua tabel. Jangan pernah sampai
        // menyentuh MySQL dev, misalnya saat tes dijalankan di container.
        if (config('database.default') !== 'sqlite') {
            throw new RuntimeException(
                'Tes harus memakai SQLite in-memory, bukan '
                . config('database.default') . '.'
            );
        }

        return parent::setUpTraits();
    }
}
