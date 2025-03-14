<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRefreshRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AuthController extends Controller
{
    public function register(UserRegisterRequest $request)
    {
        $user = User::where('username', $request->username)->exists();

        if ($user) {
            return Response::handler(
                400,
                'Failed to register',
                [],
                'The username has already been taken.'
            );
        }

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'name' => $request->name,
        ]);

        return Response::handler(
            201,
            'Registration successful',
            UserResource::make($user)
        );
    }

    public function login(UserLoginRequest $request)
    {
        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return Response::handler(
                401,
                'Failed to login',
                [],
                'Username or password is invalid.'
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
                return Response::handler(
                    401,
                    'Failed to refresh access token',
                    [],
                    'Refresh token is required'
                );
            }

            try {
                $payload = auth('api')->setToken($refreshToken)->getPayload();
            } catch (\Exception $e) {
                return Response::handler(
                    401,
                    'Failed to refresh access token',
                    [],
                    'Invalid or expired refresh token'
                );
            }

            if ($payload['type'] !== 'refresh') {
                return Response::handler(
                    401,
                    'Failed to refresh access token',
                    [],
                    'Invalid or expired refresh token'
                );
            }

            $user = User::find($payload['sub']);

            if (!$user) {
                return Response::handler(
                    401,
                    'Failed to refresh access token',
                    [],
                    'User not found'
                );
            }

            $newAccessToken = auth('api')
                ->claims(['type' => 'access'])
                ->setTTL(config('jwt.ttl'))
                ->fromUser($user);

            return Response::handler(
                200,
                'Access token refreshed successfully',
                ['access_token' => $newAccessToken]
            );
        } catch (\Exception $e) {
            return Response::handler(
                500,
                'Failed to refresh access token',
                [],
                [$e->getMessage()]
            );
        }
    }

    public function logout()
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return Response::handler(
                    401,
                    'Unauthorized',
                    [],
                    'You must be logged in to perform this action'
                );
            }

            $user->update(['token' => null]);

            auth()->logout();

            return Response::handler(
                200,
                'Logout successful'
            );
        } catch (TokenInvalidException $err) {
            return Response::handler(
                401,
                'Unauthorized',
                [],
                'Token is invalid or expired'
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Internal Server Error',
                [],
                'Something went wrong'
            );
        }
    }
}
