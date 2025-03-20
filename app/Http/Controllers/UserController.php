<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function getAll()
    {
        try {
            $users = User::withoutTrashed()->get();

            if ($users->isEmpty()) {
                return Response::handler(
                    200,
                    'Users retrieved successfully'
                );
            }

            return Response::handler(
                200,
                'Users retrieved successfully',
                UserResource::collection($users)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to retrieve users',
                [],
                $err->getMessage()
            );
        }
    }

    public function getById($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return Response::handler(
                    400,
                    'Failed to retrieve project',
                    [],
                    'User not found'
                );
            }

            return Response::handler(
                200,
                'User retrieved successfully',
                [UserResource::make($user)]
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to retrieve user',
                [],
                $err->getMessage()
            );
        }
    }

    public function update(UserUpdateRequest $request, $id)
    {
        try {
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

            return Response::handler(
                200,
                'User updated successfully',
                UserResource::make($user)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to update user',
                [],
                $err->getMessage()
            );
        }
    }

    public function softDelete($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return Response::handler(
                    400,
                    'Failed to retrieve project',
                    [],
                    'User not found'
                );
            }

            $user->delete();

            return Response::handler(200, 'User deleted successfully');
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to delete user',
                [],
                $err->getMessage()
            );
        }
    }
}
