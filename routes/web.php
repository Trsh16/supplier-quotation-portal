<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'suppliers')->name('suppliers.index');

use App\Http\Controllers\SupplierQrController;

Route::get('/supplier-qr', [SupplierQrController::class, 'index'])
    ->name('supplier-qr');

Route::get('/supplier-qr.svg', [SupplierQrController::class, 'image'])
    ->name('supplier-qr.image');
