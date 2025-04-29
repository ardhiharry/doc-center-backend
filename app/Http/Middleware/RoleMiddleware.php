<?php

namespace App\Http\Middleware;

use App\Helpers\Response;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HTTP;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): JsonResponse
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, $roles)) {
            return Response::handler(
                HTTP::HTTP_FORBIDDEN,
                'Forbidden',
                [],
                [],
                'Anda tidak memiliki izin untuk mengakses sumber daya ini.'
            );
        }

        return $next($request);
    }
}
