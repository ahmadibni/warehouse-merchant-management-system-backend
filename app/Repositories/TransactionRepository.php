<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Models\TransactionProduct;

class TransactionRepository
{
    public function getAll(array $fields)
    {
        return Transaction::select($fields)
            ->with(['transactionProducts.product', 'merchant.keeper'])
            ->latest()
            ->paginate(10);
    }

    public function getById(int $id, array $fields)
    {
        return Transaction::select($fields)
            ->with(['transactionProducts.product', 'merchant.keeper'])
            ->findOrFail($id);
    }

    public function create(array $data)
    {
        return Transaction::create($data);
    }

    public function update(int $id, array $data)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->update($data);

        return $transaction;
    }

    public function delete(int $id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->delete();
    }

    public function createTransactionProduct(int $transactionId, array $products)
    {
        foreach ($products as $product) {
            $subTotal = $product['quantity'] * $product['price'];

            TransactionProduct::create([
                'transaction_id' => $transactionId,
                'product_id' => $product['product_id'],
                'quantity' => $product['quantity'],
                'price' => $product['price'],
                'sub_total' => $subTotal,
            ]);
        }
    }

    public function getTransactionsByMerchantId(int $merchantId)
    {
        return Transaction::select(['*'])
            ->with(['transactionProducts.product', 'merchant'])
            ->where('merchant_id', $merchantId)
            ->get();
    }
}
