<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class Response
{
    public static function handler($statusCode = 200, $message = '', $data = [], $errors = []): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
            'errors' => $errors
        ], $statusCode);
    }
}
