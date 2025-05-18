<?php

namespace App\Http\Controllers;

use App\Http\Requests\MerchantProductRequest;
use App\Http\Requests\MerchantProductUpdateRequest;
use App\Services\MerchantProductService;
use Illuminate\Http\Request;

class MerchantProductController extends Controller
{
    private $merchantProductService;

    public function __construct(MerchantProductService $merchantProductService)
    {
        $this->merchantProductService = $merchantProductService;
    }

    public function store(MerchantProductRequest $request, int $merchantId)
    {
        $data = $request->validated();
        $data['merchant_id'] = $merchantId;

        $merchantProduct = $this->merchantProductService->attachProductToMerchant(
            $data
        );

        return response()->json([
            'message' => 'Product added to merchant successfully',
            'data' => $merchantProduct
        ], 201);
    }

    public function update(MerchantProductUpdateRequest $request, int $merchantId, int $productId)
    {
        $data = $request->validated();
        $merchantProduct = $this->merchantProductService->updateStock(
            $merchantId,
            $productId,
            $data['stock'],
            $data['warehouse_id'],
        );

        return response()->json([
            'message' => 'Product stock updated successfully',
            'data' => $merchantProduct
        ], 201);
    }

    public function destroy(int $merchantId, int $productId)
    {
        $this->merchantProductService->removeProductFromMerchant($merchantId, $productId);

        return response()->json([
            'message' => 'Product removed from merchant successfully'
        ], 201);
    }
}
