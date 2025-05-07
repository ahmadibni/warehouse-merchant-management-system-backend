<?php

namespace App\Http\Controllers;

use App\Http\Requests\WarehouseProductRequest;
use App\Http\Requests\WarehouseProductUpdateRequest;
use App\Services\WarehouseService;
use Illuminate\Http\Request;

class WarehouseProductController extends Controller
{
    private $warehouseService;

    public function __construct(WarehouseService $warehouseService)
    {
        $this->warehouseService = $warehouseService;
    }

    public function attach(WarehouseProductRequest $request, int $warehouseId)
    {
        $data = $request->validated();
        $this->warehouseService->attachProduct(
            $warehouseId,
            $data['product_id'],
            $data['stock']
        );

        return response()->json([
            'message' => 'Product added successfully'
        ], 201);
    }

    public function detach(int $warehouseId, int $productId)
    {
        $this->warehouseService->detachProduct($warehouseId, $productId);
        return response()->json([
            'message' => 'Product removed successfully'
        ], 201);
    }

    public function update(WarehouseProductUpdateRequest $request, int $warehouseId, int $productId)
    {
        $warehouseProduct = $this->warehouseService->updateProductStock(
            $warehouseId,
            $productId,
            $request->validated()['stock']
        );

        return response()->json([
            'message' => 'Product stock updated successfully',
            'data' => $warehouseProduct
        ], 201);
    }
}
