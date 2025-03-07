<?php

namespace App\Services;

use App\Models\User;
use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function getAllUsers(): JsonResponse
    {
        $users = User::all();
        return ResponseHelper::success(200, 'Users retrieved successfully', $users);
    }

    public function getUserById($id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return ResponseHelper::error(404, 'User not found');
        }
        return ResponseHelper::success(200, 'User retrieved successfully', $user);
    }

    public function update($request, $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return ResponseHelper::error(404, 'User not found');
        }

        $user->update($request->only(['username', 'password', 'name']));

        if ($request->password) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        return ResponseHelper::success(200, 'User updated successfully', $user);
    }

    public function softDelete($id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return ResponseHelper::error(404, 'User not found');
        }

        $user->delete();

        return ResponseHelper::success(200, 'User deleted successfully');
    }
}
