<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRoleRequest;
use App\Services\UserRoleService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    protected $userRoleService;

    public function __construct(UserRoleService $userRoleService)
    {
        $this->userRoleService = $userRoleService;
    }

    public function assignRole(UserRoleRequest $request)
    {
        $data = $request->validated();
        $user = $this->userRoleService->assignRoleToUser(
            $data['user_id'],
            $data['role_id']
        );
        return response()->json([
            'message' => 'Role assigned successfully',
            'data' => $user
        ]);
    }

    public function removeRole(UserRoleRequest $request)
    {
        $user = $this->userRoleService->removeRoleFromUser(
            $request->user_id,
            $request->role_id
        );
        return response()->json([
            'message' => 'Role removed successfully',
            'data' => $user
        ]);
    }

    public function getUserRoles(int $userId)
    {
        try {
            $roles = $this->userRoleService->getUserRoles($userId);
            return response()->json([
                'user_id' => $userId,
                'roles' => $roles
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }
    }
}
