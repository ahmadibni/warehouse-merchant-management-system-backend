<?php

namespace App\Repositories;

use App\Models\WarehouseProduct;
use Illuminate\Validation\ValidationException;

class WarehouseProductRepository
{
    public function getByWarhouseAndProduct(int $warehouseId, int $productId): ?WarehouseProduct
    {
        return WarehouseProduct::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->first();
    }

    /**
     *  Digunakan untuk mengupdate produk yang ada di warehouse
     *  ketika melakukan mengupdate produk yang ada merchant
     */
    public function updateStock(int $warehouseId, int $productId, int $stock): WarehouseProduct
    {
        $warehouseProduct = $this->getByWarhouseAndProduct($warehouseId, $productId);
        if (!$warehouseProduct) {
            throw ValidationException::withMessages([
                'product_id' => 'Product not found in this warehouse.',
            ]);
        }
        $warehouseProduct->update(['stock' => $stock]);
        return $warehouseProduct;
    }
}
