<?php

namespace App\Services;

use App\Repositories\MerchantProductRepository;
use App\Repositories\MerchantRepository;
use App\Repositories\WarehouseProductRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MerchantProductService
{
    private $merchantProductRepository;
    private $merchantRepository;
    private $warehouseProductRepository;

    public function __construct(
        MerchantProductRepository $merchantProductRepository,
        MerchantRepository $merchantRepository,
        WarehouseProductRepository $warehouseProductRepository
    ) {
        $this->merchantProductRepository = $merchantProductRepository;
        $this->warehouseProductRepository = $warehouseProductRepository;
        $this->merchantRepository = $merchantRepository;
    }

    public function attachProductToMerchant(array $data)
    {
        return DB::transaction(function () use ($data) {

            //Cek apakah produknya ada di warehouse
            $warehouseProduct = $this->warehouseProductRepository->getByWarhouseAndProduct(
                $data['warehouse_id'],
                $data['product_id']
            );

            if (!$warehouseProduct) {
                throw ValidationException::withMessages([
                    'warehouse_id' => 'Product not found in this warehouse',
                ]);
            }

            if ($warehouseProduct->stock < $data['stock']) {
                throw ValidationException::withMessages([
                    'stock' => 'Insufficient amount in this warehouse',
                ]);
            }

            //Cek apakah produk sudah ada di merchant
            $merchantProduct = $this->merchantProductRepository->getByMerchantAndProduct(
                $data['merchant_id'],
                $data['product_id']
            );

            if ($merchantProduct) {
                throw ValidationException::withMessages([
                    'product_id' => 'Product already exists in this merchant',
                ]);
            }

            //Update stock produk di warehouse
            $this->warehouseProductRepository->updateStock(
                $data['warehouse_id'],
                $data['product_id'],
                $warehouseProduct->stock - $data['stock']
            );

            //Insert produk ke merchant
            return $this->merchantProductRepository->create([
                'warehouse_id' => $data['warehouse_id'],
                'merchant_id' => $data['merchant_id'],
                'product_id' => $data['product_id'],
                'stock' => $data['stock'],
            ]);
        });
    }

    public function updateStock(int $merchantId, int $productId, int $newStock, int $warehouseId)
    {
        return DB::transaction(function () use ($merchantId, $productId, $newStock, $warehouseId) {

            //Cek apakah ada produk-nya
            $product = $this->merchantProductRepository->getByMerchantAndProduct($merchantId, $productId);

            if (!$product) {
                throw ValidationException::withMessages([
                    'product_id' => 'Product not found in this merchant',
                ]);
            }

            if (!$warehouseId) {
                throw ValidationException::withMessages([
                    'warehouse_id' => ['Warehouse ID is required'],
                ]);
            }

            $currentStock = $product->stock;

            //Jika ingin menambah stock
            if ($newStock > $currentStock) {
                $diff = $newStock - $currentStock;

                //Cek apakah ada produk-nya di warehouse
                $warehouseProduct = $this->warehouseProductRepository->getByWarhouseAndProduct($warehouseId, $productId);

                if (!$warehouseProduct) {
                    throw ValidationException::withMessages([
                        'warehouse_id' => ['Product not found in this warehouse'],
                    ]);
                }

                if ($warehouseProduct->stock < $diff) {
                    throw ValidationException::withMessages([
                        'stock' => ['Insufficient amount in this warehouse'],
                    ]);
                }

                //Kurangi stock di warehouse
                $this->warehouseProductRepository->updateStock(
                    $warehouseId,
                    $productId,
                    $warehouseProduct->stock - $diff
                );
            }

            //Jika ingin mengurangi stock
            if ($newStock < $currentStock) {
                $diff = $currentStock - $newStock;

                //Cek apakah ada produk-nya di warehouse
                $warehouseProduct = $this->warehouseProductRepository->getByWarhouseAndProduct($warehouseId, $productId);

                if (!$warehouseProduct) {
                    throw ValidationException::withMessages([
                        'warehouse_id' => ['Product not found in this warehouse'],
                    ]);
                }

                //Tambah stock di warehouse
                $this->warehouseProductRepository->updateStock(
                    $warehouseId,
                    $productId,
                    $warehouseProduct->stock + $diff
                );
            }

            return $this->merchantProductRepository->updateStock(
                $merchantId,
                $productId,
                $newStock
            );
        });
    }

    public function removeProductFromMerchant(int $merchantId, int $productId)
    {
        //Cek apakah ada merchant-nya
        $merchant = $this->merchantRepository->getById($merchantId, ['id']);

        if (!$merchant) {
            throw ValidationException::withMessages([
                'merchant_id' => 'Merchant not found',
            ]);
        }

        //Cek apakah ada produk-nya
        $product = $this->merchantProductRepository->getByMerchantAndProduct($merchantId, $productId);

        if (!$product) {
            throw ValidationException::withMessages([
                'product_id' => 'Product not found in this merchant',
            ]);
        }

        $merchant->products()->detach($productId);
    }
}
