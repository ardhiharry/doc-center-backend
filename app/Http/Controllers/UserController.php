<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function getAll(Request $request): JsonResponse
    {
        try {
            $users = User::withoutTrashed()
                ->orderBy('name', 'asc')
                ->paginate($request->query('limit', 10));

            if ($users->isEmpty()) {
                return Response::handler(
                    200,
                    'Berhasil mengambil data pengguna'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data pengguna',
                UserResource::collection($users),
                Response::pagination($users)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data pengguna',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $query = User::withoutTrashed();

            foreach ($request->all() as $key => $value) {
                if (in_array($key, ['username', 'name'])) {
                    $query->where($key, 'LIKE', "%{$value}%");
                }
            }

            $users = $query->orderBy('name', 'asc')
                ->paginate($request->query('limit', 10));

            if ($users->isEmpty()) {
                return Response::handler(
                    200,
                    'Berhasil mengambil data pengguna'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data pengguna',
                UserResource::collection($users),
                Response::pagination($users)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data pengguna',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function getById($id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return Response::handler(
                    400,
                    'Gagal mengambil data pengguna',
                    [],
                    [],
                    'Data pengguna tidak ditemukan.'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data pengguna',
                [UserResource::make($user)]
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data pengguna',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function update(UserUpdateRequest $request, $id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return Response::handler(
                    400,
                    'Gagal mengubah data pengguna',
                    [],
                    [],
                    'Data pengguna tidak ditemukan.'
                );
            }

            if ($request->username !== $user->username) {
                if (User::where('username', $request->username)
                    ->where('id', '!=', $id)
                    ->exists()
                ) {
                    return Response::handler(
                        400,
                        'Gagal mengubah data pengguna',
                        [],
                        [],
                        ['username' => ['Username sudah ada.']]
                    );
                }
            }

            $data = $request->only(['username', 'name']);

            if ($request->has('is_process')) {
                $data['is_process'] = $request->boolean('is_process');
            }

            if ($request->filled('old_password') || $request->filled('new_password') || $request->filled('confirm_new_password')) {
                if (!$request->filled(['old_password', 'new_password', 'confirm_new_password'])) {
                    return Response::handler(
                        400,
                        'Gagal mengubah data pengguna',
                        [],
                        [],
                        'Semua password harus diisi.'
                    );
                }

                if (!Hash::check($request->old_password, $user->password)) {
                    return Response::handler(
                        400,
                        'Gagal mengubah data pengguna',
                        [],
                        [],
                        'Password lama salah.'
                    );
                }

                if ($request->new_password !== $request->confirm_new_password) {
                    return Response::handler(
                        400,
                        'Gagal mengubah data pengguna',
                        [],
                        [],
                        'Konfirmasi password tidak cocok.'
                    );
                }

                $data['password'] = Hash::make($request->new_password);
            }

            $user->update($data);

            return Response::handler(
                200,
                'Berhasil mengubah data pengguna',
                UserResource::make($user->refresh())
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengubah data pengguna',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function softDelete($id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return Response::handler(
                    400,
                    'Gagal menghapus data pengguna',
                    [],
                    [],
                    'Data pengguna tidak ditemukan.'
                );
            }

            $user->delete();

            return Response::handler(200, 'Berhasil menghapus data pengguna');
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal menghapus data pengguna',
                [],
                [],
                $err->getMessage()
            );
        }
    }
}
