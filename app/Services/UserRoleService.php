<?php

namespace App\Services;

use App\Repositories\UserRoleRepository;

class UserRoleService
{
    protected $userRoleRepository;

    public function __construct(UserRoleRepository $userRoleRepository)
    {
        $this->userRoleRepository = $userRoleRepository;
    }

    public function assignRoleToUser(int $userId, int $roleId)
    {
        return $this->userRoleRepository->assignRoleToUser($userId, $roleId);
    }

    public function removeRoleFromUser(int $userId, int $roleId)
    {
        return $this->userRoleRepository->removeRoleFromUser($userId, $roleId);
    }

    public function getUserRoles(int $userId)
    {
        return $this->userRoleRepository->getUserRoles($userId);
    }
}
