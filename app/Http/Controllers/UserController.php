<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        $fields = ['id', 'name', 'email', 'photo', 'phone'];
        $users = $this->userService->getAll($fields);
        return response()->json(UserResource::collection($users));
    }

    public function show($id)
    {
        try {
            $fields = ['id', 'name', 'email', 'photo', 'phone'];
            $user = $this->userService->getById($id, $fields);
            return response()->json(new UserResource($user));
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }
    }

    public function store(UserRequest $request)
    {
        $data = $request->validated();
        $user = $this->userService->create($data);
        return response()->json(new UserResource($user), 201);
    }

    public function update(UserRequest $request, int $id)
    {
        try {
            $data = $request->validated();
            $user = $this->userService->update($id, $data);
            return response()->json(new UserResource($user), 201);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->userService->delete($id);
            return response()->json([
                'message' => 'User deleted successfully'
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }
    }
}
