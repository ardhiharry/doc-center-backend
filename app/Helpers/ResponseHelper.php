<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ResponseHelper
{
    public static function success($statusCode = 200, $message = '', $data = []): JsonResponse
    {
        return response()->json([
            'statusCode' => $statusCode,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    public static function error($statusCode = 400, $message = '', $errors = []): JsonResponse
    {
        return response()->json([
            'statusCode' => $statusCode,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }
}
