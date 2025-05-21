<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Http\Resources\RoleResource;
use App\Services\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    private $roleService;
    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function index()
    {
        $roles = $this->roleService->getAll(['id', 'name']);
        return response()->json(RoleResource::collection($roles), 201);
    }

    public function show(int $id)
    {
        $role = $this->roleService->getById($id, ['id', 'name']);
        return response()->json(new RoleResource($role), 201);
    }

    public function store(RoleRequest $request)
    {
        $data = $request->validated();
        $role = $this->roleService->create($data);
        return response()->json(new RoleResource($role), 201);
    }

    public function update (RoleRequest $request, int $id)
    {
        $data = $request->validated();
        $role = $this->roleService->update($id, $data);
        return response()->json(new RoleResource($role), 201);
    }

    public function destroy(int $id)
    {
        $this->roleService->delete($id);
        return response()->json([
            'message' => 'Role deleted successfully'
        ], 201);
    }
}
