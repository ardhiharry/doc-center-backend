<?php

namespace App\Helpers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

class Response
{
    public static function handler($status = 200, $message = '', $data = [], $pagination = [], $errors = []): JsonResponse
    {
        if ($status === 500) {
            return response()->json([
                'status' => $status,
                'message' => $message,
                'data' => $data,
                'pagination' => $pagination,
                'errors' => $errors
            ], 500);
        }

        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data,
            'pagination' => $pagination,
            'errors' => $errors
        ]);
    }

    public static function pagination(LengthAwarePaginator $data): array
    {
        $query = request()->query();
        unset($query['page']);

        return [
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
            'per_page' => $data->perPage(),
            'total' => $data->total(),
            'next_page_url' => $data->nextPageUrl() ? $data->nextPageUrl() . '&' . http_build_query($query) : null,
            'prev_page_url' => $data->previousPageUrl() ? $data->previousPageUrl() . '&' . http_build_query($query) : null,
        ];
    }
}
