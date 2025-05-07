<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'status' => 200,
        'message' => 'Welcome to the Document Center API',
        'data' => [],
        'pagination' => [],
        'errors' => []
    ]);
});
