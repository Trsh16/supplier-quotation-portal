<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StandardGoodController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Middleware\ProcurementBasicAuth;

// API internal: berisi data kontak supplier, jadi wajib terautentikasi.
Route::middleware([ProcurementBasicAuth::class, 'throttle:60,1'])->group(function () {
    Route::get('/standard-goods', [StandardGoodController::class, 'index']);
    Route::get('/measurement-units', [StandardGoodController::class, 'units']);

    Route::get('/suppliers', [SupplierController::class, 'index']);
    Route::post('/suppliers', [SupplierController::class, 'store']);
});
