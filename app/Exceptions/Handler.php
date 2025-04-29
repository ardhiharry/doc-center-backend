<?php

namespace App\Exceptions;

use App\Helpers\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as HTTP;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
        if ($e instanceof TokenInvalidException
            || $e instanceof TokenExpiredException
            || $e instanceof UnauthorizedHttpException
            || $e->getMessage() === 'Route [login] not defined.'
        ) {
            return Response::handler(
                HTTP::HTTP_UNAUTHORIZED,
                'Unauthorized',
                [],
                ['token' => ['Token tidak valid atau kadaluarsa.']]
            );
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return Response::handler(
                HTTP::HTTP_METHOD_NOT_ALLOWED,
                'Permintaan tidak diizinkan',
                [],
                [],
                "Method {$request->method()} tidak diizinkan"
            ) ;
        }

        if ($e instanceof ValidationException) {
            return Response::handler(
                HTTP::HTTP_BAD_REQUEST,
                'Kesalahan validasi',
                [],
                [],
                $e->validator->errors()
            );
        }

        if ($e instanceof NotFoundHttpException) {
            return Response::handler(
                HTTP::HTTP_NOT_FOUND,
                'Kesalahan mengakses halaman',
                [],
                [],
                'Halaman tidak ditemukan'
            );
        }

        return Response::handler(
            HTTP::HTTP_INTERNAL_SERVER_ERROR,
            'Terjadi kesalahan pada server',
            [],
            [],
            [
                'message' => [$e->getMessage()],
                'file' => [$e->getFile()],
                'line' => [$e->getLine()]
            ]
        );
    }
}
