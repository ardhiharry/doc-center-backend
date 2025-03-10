<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AuthController extends Controller
{
    public function register(UserRegisterRequest $request)
    {
        $isUserExists = User::where('username', $request->username)->exists();
        if ($isUserExists) {
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

        cookie()->queue(cookie(
            'refresh_token',
            $refreshToken,
            config('jwt.refresh_ttl'),
            '/',
            null,
            true,
            true,
            false,
            'Strict'
        ));

        return ResponseHelper::success(
            200,
            'Login successful',
            [
                'username' => $user->username,
                'name' => $user->name,
                'access_token' => $accessToken
            ]
        );
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
