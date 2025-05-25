<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Services\TransactionService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    private $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function index()
    {
        $fields = ['id', 'name', 'grand_total', 'merchant_id'];
        $transactions = $this->transactionService->getAll($fields);
        return response()->json(TransactionResource::collection($transactions));
    }

    public function store(TransactionRequest $request)
    {
        $data = $request->validated();
        $transaction = $this->transactionService->createTransaction($data);

        return response()->json([
            'message' => 'Transaction created successfully',
            'data' => $transaction
        ], 201);
    }

    public function show(int $id)
    {
        try {
            $fields = ['*'];
            $transaction = $this->transactionService->getTransactionById($id, $fields);
            return response()->json(new TransactionResource($transaction));
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Transaction not found'
            ], 404);
        }
    }

    public function getTransactionsByMerchant()
    {
        $user = auth()->user;

        if (!$user || !$user->merchant) {
            return response()->json([
                'message' => 'User does not have a merchant'
            ], 404);
        }

        $merchantId = $user->merchant->id;
        $transactions = $this->transactionService->getByMerchantId($merchantId);

        return response()->json(TransactionResource::collection($transactions));
    }
}
