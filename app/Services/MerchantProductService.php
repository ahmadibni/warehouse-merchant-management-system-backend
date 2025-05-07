<?php

namespace App\Services;

use App\Repositories\MerchantProductRepository;
use App\Repositories\WarehouseProductRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MerchantProductService
{
    private $merchantProductRepository;
    private $warehouseProductRepository;

    public function __construct(
        MerchantProductRepository $merchantProductRepository,
        WarehouseProductRepository $warehouseProductRepository
    ) {
        $this->merchantProductRepository = $merchantProductRepository;
        $this->warehouseProductRepository = $warehouseProductRepository;
    }

    public function attachProductToMerchant(array $data)
    {

        return DB::transaction(function () use ($data){
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
            $existedMerchantProduct = $this->merchantProductRepository->getByMerchantAndProduct(
                $data['merchant_id'],
                $data['product_id']
            );

            if ($existedMerchantProduct) {
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
}
