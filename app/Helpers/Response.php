<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class Response
{
    public static function handler($status = 200, $message = '', $data = [], $errors = []): JsonResponse
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data,
            'errors' => $errors
        ], $status);
    }
}
