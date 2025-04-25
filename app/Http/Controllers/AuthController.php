<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRefreshRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AuthController extends Controller
{
    public function register(UserRegisterRequest $request): JsonResponse
    {
        try {
            $user = User::where('username', $request->username)->exists();

            if ($user) {
                return Response::handler(
                    400,
                    'Gagal mendaftar',
                    [],
                    [],
                    ['username' => ['Username sudah terdaftar.']]
                );
            }

            $user = User::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'name' => $request->name,
            ]);

            return Response::handler(
                201,
                'Berhasil mendaftar',
                UserResource::make($user)
            );
        } catch (\Exception $e) {
            return Response::handler(
                500,
                'Gagal mendaftar',
                [],
                [],
                $e->getMessage()
            );
        }
    }

    public function login(UserLoginRequest $request): JsonResponse
    {
        try {
            $user = User::where('username', $request->username)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return Response::handler(
                    401,
                    'Gagal login',
                    [],
                    [],
                    'Username atau password salah.'
                );
            }

            $accessToken = auth('api')
                ->claims(['type' => 'access'])
                ->setTTL((int) config('jwt.ttl'))
                ->fromUser($user);

            $refreshToken = auth('api')
                ->claims(['type' => 'refresh'])
                ->setTTL((int) config('jwt.refresh_ttl'))
                ->fromUser($user);

            $user->update(['token' => $refreshToken]);

            return Response::handler(
                200,
                'Berhasil login',
                [
                    'id' => $user->id,
                    'username' => $user->username,
                    'name' => $user->name,
                    'role' => $user->role,
                    'refresh_token' => $user->token,
                    'access_token' => $accessToken
                ]
            );
        } catch (\Exception $e) {
            return Response::handler(
                500,
                'Gagal login',
                [],
                [],
                $e->getMessage()
            );
        }
    }

    public function refreshToken(UserRefreshRequest $request): JsonResponse
    {
        try {
            $refreshToken = $request->input('refresh_token');

            if (!$refreshToken) {
                return Response::handler(
                    401,
                    'Gagal refresh access token',
                    [],
                    [],
                    'Refresh token diperlukan.'
                );
            }

            try {
                $payload = auth('api')->setToken($refreshToken)->getPayload();
            } catch (\Exception $e) {
                return Response::handler(
                    401,
                    'Gagal refresh access token',
                    [],
                    [],
                    'Refresh token tidak valid atau kadaluarsa.'
                );
            }

            if ($payload['type'] !== 'refresh') {
                return Response::handler(
                    401,
                    'Gagal refresh access token',
                    [],
                    [],
                    'Refresh token tidak valid atau kadaluarsa.'
                );
            }

            $user = User::find($payload['sub']);

            if (!$user) {
                return Response::handler(
                    401,
                    'Gagal refresh access token',
                    [],
                    [],
                    'Data pengguna tidak ditemukan.'
                );
            }

            $newAccessToken = auth('api')
                ->claims(['type' => 'access'])
                ->setTTL(config('jwt.ttl'))
                ->fromUser($user);

            return Response::handler(
                200,
                'Berhasil refresh access token',
                ['access_token' => $newAccessToken]
            );
        } catch (\Exception $e) {
            return Response::handler(
                500,
                'Gagal refresh access token',
                [],
                [],
                [$e->getMessage()]
            );
        }
    }

    public function logout(): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return Response::handler(
                    401,
                    'Unauthorized',
                    [],
                    [],
                    'Anda harus login untuk melakukan tindakan ini.'
                );
            }

            $user->update(['token' => null]);

            auth()->logout();

            return Response::handler(
                200,
                'Berhasil logout',
            );
        } catch (TokenInvalidException $err) {
            return Response::handler(
                401,
                'Unauthorized',
                [],
                [],
                'Token tidak valid atau kadaluarsa.'
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Internal Server Error',
                [],
                [],
                'Something went wrong'
            );
        }
    }
}
