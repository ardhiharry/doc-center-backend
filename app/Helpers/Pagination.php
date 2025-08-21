<?php

namespace App\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;

class Pagination
{
    public static function paginate(LengthAwarePaginator $data): array
    {
        return [
            'limit' => $data->perPage(),
            'total' => $data->total(),
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
            'next_page_url' => $data->nextPageUrl(),
            'prev_page_url' => $data->previousPageUrl(),
        ];
    }
}
