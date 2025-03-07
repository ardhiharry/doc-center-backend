<?php

namespace App\Http\Middleware;

use App\Helpers\ResponseHelper;
use Closure;
use Illuminate\Http\Request;

class RefreshToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $token = auth()->parseToken();
            $user = auth()->authenticate();

            if (!$user) {
                return response()->json([
                    'statusCode' => 401,
                    'message' => 'Invalid user',
                ], 401);
            }

            if ($token->getClaim('type') === 'access') {
                return $next($request);
            }

            // Get Refresh Token from Cookie
            $refreshToken = $request->cookie('refresh_token');

            if (!$refreshToken) {
                return ResponseHelper::error(
                    401,
                    'Refresh token not found'
                );
            }

            // Check Refresh Token in DB
            if ($user->token !== $refreshToken) {
                return ResponseHelper::error(
                    401,
                    'Invalid refresh token'
                );
            }

            // Generate New Access Token
            auth()->setToken($refreshToken);
            $newAccessToken = auth()->refresh();

            $request->headers->set('Authorization', 'Bearer ' . $newAccessToken);

            $response = $next($request);
            $response->headers->set('Authorization', 'Bearer ' . $newAccessToken);

            return $response;
        } catch (\Exception $err) {
            return response()->json([
                'statusCode' => 401,
                'message' => 'Token expired or invalid',
            ], 401);
        }
    }
}
