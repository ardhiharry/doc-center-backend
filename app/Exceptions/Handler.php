<?php

namespace App\Exceptions;

use App\Helpers\Response;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
        if ($e instanceof TokenInvalidException || $e instanceof TokenExpiredException || $e->getMessage() === 'Route [login] not defined.') {
            return Response::handler(
                401,
                'Unauthorized',
                [],
                ['token' => ['Token tidak valid atau kadaluarsa.']]
            );
        }

        if ($e instanceof UnauthorizedHttpException) {
            return Response::handler(
                401,
                'Unauthorized',
                [],
                ['token' => ['Token tidak valid atau kadaluarsa.']]
            );
        }

        return parent::render($request, $e);
    }
}
