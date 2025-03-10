<?php

use App\Http\Controllers\AdminDocCategoryController;
use App\Http\Controllers\AdminDocController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Users
    Route::get('/users', [UserController::class, 'getAll']);
    Route::get('/users/{id}', [UserController::class, 'getById']);
    Route::patch('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'softDelete']);
});

// Projects
Route::post('/projects', [ProjectController::class, 'create']);
Route::get('/projects', [ProjectController::class, 'getAll']);
Route::get('/projects/{id}', [ProjectController::class, 'getById']);
Route::patch('/projects/{id}', [ProjectController::class, 'update']);
Route::delete('/projects/{id}', [ProjectController::class, 'softDelete']);

// Admin Doc Category
Route::post('/admin-doc-categories', [AdminDocCategoryController::class, 'create']);
Route::get('/admin-doc-categories', [AdminDocCategoryController::class, 'getAll']);
Route::get('/admin-doc-categories/{id}', [AdminDocCategoryController::class, 'getById']);
Route::patch('/admin-doc-categories/{id}', [AdminDocCategoryController::class, 'update']);
Route::delete('/admin-doc-categories/{id}', [AdminDocCategoryController::class, 'softDelete']);

// Admin Docs
Route::post('/admin-docs', [AdminDocController::class, 'create']);
Route::get('/admin-docs', [AdminDocController::class, 'getAll']);
Route::get('/admin-docs/{id}', [AdminDocController::class, 'getById']);
Route::delete('/admin-docs/{id}', [AdminDocController::class, 'softDelete']);
