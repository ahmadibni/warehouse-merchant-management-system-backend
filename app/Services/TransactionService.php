<?php

namespace App\Services;

use App\Repositories\TransactionRepository;

class TransactionService
{
    private $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    public function getAll(array $fields)
    {
        return $this->transactionRepository->getAll($fields);
    }

    public function getById(int $id, array $fields)
    {
        return $this->transactionRepository->getById($id, $fields);
    }

    public function create(array $data)
    {
        $transaction = $this->transactionRepository->create($data);
        $this->transactionRepository->createTransactionProduct($transaction->id, $data['products']);

        return $transaction;
    }
}
