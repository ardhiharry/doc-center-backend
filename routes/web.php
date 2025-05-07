<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'status' => 200,
        'message' => 'Welcome to the HMA Project Manager API',
        'data' => [],
        'pagination' => [],
        'errors' => []
    ]);
});
