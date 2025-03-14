<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function getAll()
    {
        $users = User::withoutTrashed()->get();

        if ($users->isEmpty()) {
            return Response::handler(
                200,
                'Users retrieved successfully'
            );
        }

        return Response::handler(200, 'Users retrieved successfully', $users);
    }

    public function getById($id)
    {
        $user = User::find($id);

        if (!$user) {
            return Response::handler(
                400,
                'Failed to retrieve project',
                [],
                'User not found'
            );
        }

        return Response::handler(200, 'User retrieved successfully', [$user]);
    }

    public function update(UserUpdateRequest $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return Response::handler(
                400,
                'Failed to retrieve project',
                [],
                'User not found'
            );
        }

        $data = $request->only(['username', 'name']);

        if ($request->filled('old_password') || $request->filled('new_password') || $request->filled('confirm_new_password')) {
            if (!$request->filled(['old_password', 'new_password', 'confirm_new_password'])) {
                return Response::handler(
                    400,
                    'Failed to update user',
                    [],
                    'All password fields are required'
                );
            }

            if (!Hash::check($request->old_password, $user->password)) {
                return Response::handler(
                    400,
                    'Failed to update user',
                    [],
                    'Old password is incorrect'
                );
            }

            if ($request->new_password !== $request->confirm_new_password) {
                return Response::handler(
                    400,
                    'Failed to update user',
                    [],
                    'New password confirmation does not match'
                );
            }

            $data['password'] = Hash::make($request->new_password);
        }

        $user->update($data);

        return Response::handler(200, 'User updated successfully', $user);
    }

    public function softDelete($id)
    {
        $user = User::find($id);

        if (!$user) {
            return Response::handler(
                400,
                'Failed to retrieve project'
                [],
                'User not found'
            );
        }

        $user->delete();

        return Response::handler(200, 'User deleted successfully');
    }
}
