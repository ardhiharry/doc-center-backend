<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, $roles)) {
            return \App\Helpers\Response::handler(
                Response::HTTP_FORBIDDEN,
                'Forbidden',
                [],
                [],
                'Anda tidak memiliki izin untuk mengakses sumber daya ini.'
            );
        }

        return $next($request);
    }
}
