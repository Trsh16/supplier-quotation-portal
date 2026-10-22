<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\ProcurementDashboardController::class, 'index'])->name('procurement.dashboard');

use App\Http\Controllers\SupplierQrController;
use App\Http\Controllers\SupplierOfferController;
use App\Http\Controllers\ProcurementDashboardController;

Route::get('/supplier-qr', [SupplierQrController::class, 'index'])
    ->name('supplier-qr');

Route::get('/supplier-qr.svg', [SupplierQrController::class, 'image'])
    ->name('supplier-qr.image');

Route::get('/supplier-offer', [SupplierOfferController::class, 'create'])
    ->name('supplier-offer.create');

Route::post('/supplier-offer', [SupplierOfferController::class, 'store'])
    ->name('supplier-offer.store');

Route::get('/supplier-offer/receipt/{token}', [SupplierOfferController::class, 'receipt'])
    ->name('supplier-offer.receipt');

Route::get('/', [ProcurementDashboardController::class, 'index'])
    ->name('procurement.dashboard');

Route::get('/suppliers', [ProcurementDashboardController::class, 'index'])
    ->name('suppliers.index');

Route::delete('/quotations/{quotation}', [
    \App\Http\Controllers\ProcurementDashboardController::class,
    'destroy'
])->name('quotations.destroy');