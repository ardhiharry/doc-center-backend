<?php

namespace App\Exceptions;

use App\Helpers\ResponseHelper;
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
            return ResponseHelper::error(
                401,
                'Unauthorized',
                ['Token is invalid or expired']
            );
        }

        if ($e instanceof UnauthorizedHttpException) {
            return ResponseHelper::error(
                401,
                'Unauthorized',
            );
        }

        return parent::render($request, $e);
    }
}
