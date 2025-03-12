<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRefreshRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AuthController extends Controller
{
    public function register(UserRegisterRequest $request)
    {
        $user = User::where('username', $request->username)->exists();

        if ($user) {
            return ResponseHelper::error(
                400,
                'Failed to register',
                ['username' => ['The username has already been taken.']]
            );
        }

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'name' => $request->name,
        ]);

        return ResponseHelper::success(
            201,
            'Registration successful',
            UserResource::make($user)
        );
    }

    public function login(UserLoginRequest $request)
    {
        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return ResponseHelper::error(
                401,
                'Failed to login',
                ['Username or password is invalid.']
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

        return ResponseHelper::success(
            200,
            'Login successful',
            [
                'username' => $user->username,
                'name' => $user->name,
                'refresh_token' => $user->token,
                'access_token' => $accessToken
            ]
        );
    }

    public function refreshToken(UserRefreshRequest $request)
    {
        try {
            $refreshToken = $request->input('refresh_token');

            if (!$refreshToken) {
                return ResponseHelper::error(401, 'Refresh token not provided');
            }

            try {
                $payload = auth('api')->setToken($refreshToken)->getPayload();
            } catch (\Exception $e) {
                return ResponseHelper::error(401, 'Invalid or expired refresh token');
            }

            if ($payload['type'] !== 'refresh') {
                return ResponseHelper::error(401, 'Invalid refresh token');
            }

            $user = User::find($payload['sub']);

            if (!$user) {
                return ResponseHelper::error(401, 'User not found');
            }

            $newAccessToken = auth('api')
                ->claims(['type' => 'access'])
                ->setTTL(config('jwt.ttl'))
                ->fromUser($user);

            return ResponseHelper::success(
                200,
                'Access token refreshed successfully',
                ['access_token' => $newAccessToken]
            );
        } catch (\Exception $e) {
            return ResponseHelper::error(500, 'Failed to refresh access token', [$e->getMessage()]);
        }
    }

    public function logout()
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return ResponseHelper::error(
                    401,
                    'Unauthorized',
                    ['You must be logged in to perform this action']
                );
            }

            $user->update(['token' => null]);

            auth()->logout();

            return ResponseHelper::success(
                200,
                'Logout successful'
            );
        } catch (TokenInvalidException $err) {
            return ResponseHelper::error(
                401,
                'Unauthorized',
                ['Token is invalid or expired']
            );
        } catch (\Exception $err) {
            return ResponseHelper::error(
                500,
                'Internal Server Error',
                ['Something went wrong']
            );
        }
    }
}
