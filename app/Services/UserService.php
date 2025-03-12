<?php

namespace App\Services;

use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function getAllUsers()
    {
        $users = User::withoutTrashed()->get();

        if ($users->isEmpty()) {
            return ResponseHelper::success(
                204
            );
        }

        return ResponseHelper::success(200, 'Users retrieved successfully', $users);
    }

    public function getUserById($id)
    {
        $user = User::find($id);

        if (!$user) {
            return ResponseHelper::error(404, 'User not found');
        }

        return ResponseHelper::success(200, 'User retrieved successfully', $user);
    }

    public function update(UserUpdateRequest $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return ResponseHelper::error(404, 'User not found');
        }

        $data = $request->only(['username', 'name']);

        if ($request->filled('old_password') || $request->filled('new_password') || $request->filled('confirm_new_password')) {
            if (!$request->filled(['old_password', 'new_password', 'confirm_new_password'])) {
                return ResponseHelper::error(400, 'All password fields are required');
            }

            if (!Hash::check($request->old_password, $user->password)) {
                return ResponseHelper::error(400, 'Old password is incorrect');
            }

            if ($request->new_password !== $request->confirm_new_password) {
                return ResponseHelper::error(400, 'New password confirmation does not match');
            }

            $data['password'] = Hash::make($request->new_password);
        }

        $user->update($data);

        return ResponseHelper::success(200, 'User updated successfully', $user);
    }

    public function softDelete($id)
    {
        $user = User::find($id);
        if (!$user) {
            return ResponseHelper::error(404, 'User not found');
        }

        $user->delete();

        return ResponseHelper::success(200, 'User deleted successfully');
    }
}
