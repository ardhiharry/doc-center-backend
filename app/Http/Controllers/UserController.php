<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function getAll()
    {
        $users = User::withoutTrashed()->get();

        if ($users->isEmpty()) {
            return ResponseHelper::success(
                204
            );
        }

        return ResponseHelper::success(200, 'Users retrieved successfully', $users);
    }

    public function getById($id)
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
