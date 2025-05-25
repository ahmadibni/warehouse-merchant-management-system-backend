<?php

namespace App\Services;

use App\Repositories\MerchantProductRepository;
use App\Repositories\MerchantRepository;
use App\Repositories\ProductRepository;
use App\Repositories\TransactionRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    private $transactionRepository;
    private $merchantProductRepository;
    private $productRepository;
    private $merchantRepository;

    public function __construct(
        TransactionRepository $transactionRepository,
        MerchantProductRepository $merchantProductRepository,
        ProductRepository $productRepository,
        MerchantRepository $merchantRepository
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->merchantProductRepository = $merchantProductRepository;
        $this->productRepository = $productRepository;
        $this->merchantRepository = $merchantRepository;
    }

    public function getAll(array $fields)
    {
        return $this->transactionRepository->getAll($fields);
    }

    public function getTransactionById(int $id, array $fields)
    {
        $transaction = $this->transactionRepository->getById($id, $fields);

        if (!$transaction) {
            throw ValidationException::withMessages([
                'transaction_id' => 'Transaction not found',
            ]);
        }

        return $transaction;
    }

    public function getByMerchantId(int $merchantId)
    {
        return $this->transactionRepository->getTransactionsByMerchantId($merchantId);
    }

    public function createTransaction(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Cek apakah merchant-nya ada
            $merchant = $this->merchantRepository->getById($data['merchant_id'], ['id', 'keeper_id']);

            if (!$merchant) {
                throw ValidationException::withMessages([
                    'merchant_id' => 'Merchant not found',
                ]);
            }

            // Cek apakah user yang membuat transaksi adalah pemilik merchant
            if (Auth::id() !== $merchant->keeper_id) {
                throw ValidationException::withMessages([
                    'authorization' => 'Unauthorized: You do not have permission to create transactions for this merchant.',
                ]);
            }

            $products = [];
            $subTotal = 0;

            foreach ($data['products'] as $productData) {
                // Cek apakah produk ada di merchant
                $merchantProduct = $this->merchantProductRepository->getByMerchantAndProduct(
                    $data['merchant_id'],
                    $productData['product_id']
                );

                if (!$merchantProduct || $merchantProduct->stock < $productData['quantity']) {
                    throw ValidationException::withMessages([
                        'products' => 'Insufficient stock for product ID ' . $productData['product_id'],
                    ]);
                }

                // Cek apakah produk ada di database
                $product = $this->productRepository->getById($productData['product_id'], ['id', 'price']);

                if (!$product) {
                    throw ValidationException::withMessages([
                        'products' => 'Product ID ' . $productData['product_id'] . ' not found',
                    ]);
                }

                // Hitung sub total untuk produk ini
                $price = $product->price;
                $productSubTotal = $productData['quantity'] * $price;
                $subTotal += $productSubTotal;

                // Tambahkan produk ke array
                $products[] = [
                    'product_id' => $productData['product_id'],
                    'quantity' => $productData['quantity'],
                    'price' => $price,
                    'sub_total' => $productSubTotal,
                ];

                // Kurangi stock produk di merchant
                $newStock = max(0, $merchantProduct->stock - $productData['quantity']);
                $this->merchantProductRepository->updateStock(
                    $data['merchant_id'],
                    $productData['product_id'],
                    $newStock
                );
            }

            $taxTotal = $subTotal * 0.1; // Assuming a 10% tax rate
            $grandTotal = $subTotal + $taxTotal;

            $transaction = $this->transactionRepository->create([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'sub_total' => $subTotal,
                'tax_total' => $taxTotal,
                'grand_total' => $grandTotal,
                'merchant_id' => $data['merchant_id'],
            ]);


            $this->transactionRepository->createTransactionProduct($transaction->id, $products);

            return $transaction->fresh();
        });
    }
}
