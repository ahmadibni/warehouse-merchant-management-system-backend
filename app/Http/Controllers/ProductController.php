<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Repositories\ProductService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index()
    {
        $fields = ['name', 'thumbnail', 'price'];
        $products = $this->productService->getAll($fields);
        return response()->json(ProductResource::collection($products));
    }

    public function show($id)
    {
        try {
            $fields = ['name', 'thumbnail', 'price'];
            $product = $this->productService->getById($id, $fields);
            return response()->json(new ProductResource($product));
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }
    }

    public function store(ProductRequest $request)
    {
        $product = $this->productService->create($request->validated());
        return response()->json(new ProductResource($product), 201);
    }

    public function update(ProductRequest $request, int $id)
    {
        try {
            $product = $this->productService->update($id, $request->validated());
            return response()->json(new ProductResource($product), 201);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->productService->delete($id);
            return response()->json([
                'message' => 'Product deleted successfully'
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }
    }
}
