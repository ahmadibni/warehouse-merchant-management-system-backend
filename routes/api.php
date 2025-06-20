<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MerchantController;
use App\Http\Controllers\MerchantProductController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WarehouseProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('categories', CategoryController::class);
Route::apiResource('products', ProductController::class);
Route::apiResource('warehouses', WarehouseController::class);
Route::apiResource('merchants', MerchantController::class);

Route::post('warehouses/{warehouse}/products', [WarehouseProductController::class, 'attach']);
Route::put('warehouses/{warehouse}/products/{product}', [WarehouseProductController::class, 'update']);
Route::delete('warehouses/{warehouse}/products/{product}', [WarehouseProductController::class, 'detach']);

Route::post('merchants/{merchant}/products', [MerchantProductController::class, 'store']);
Route::put('merchants/{merchant}/products/{product}', [MerchantProductController::class, 'update']);
Route::delete('merchants/{merchant}/products/{product}', [MerchantProductController::class, 'destroy']);
