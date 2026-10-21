<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StandardGoodController;
use App\Http\Controllers\Api\SupplierController;

Route::get('/standard-goods', [StandardGoodController::class, 'index']);
Route::get('/measurement-units', [StandardGoodController::class, 'units']);

Route::get('/suppliers', [SupplierController::class, 'index']);
Route::post('/suppliers', [SupplierController::class, 'store']);
