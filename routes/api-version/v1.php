<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ActivityDocCategoryController;
use App\Http\Controllers\ActivityDocController;
use App\Http\Controllers\AdminDocCategoryController;
use App\Http\Controllers\AdminDocController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    // Auth
    Route::post('/auth/refresh', [AuthController::class, 'refreshToken']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Users
    Route::get('/users', [UserController::class, 'getAll'])->middleware('role:SUPERADMIN,ADMIN,USER');
    Route::get('/users/search', [UserController::class, 'search']);
    Route::get('/users/{id}', [UserController::class, 'getById']);
    Route::patch('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'softDelete']);

    // Companies
    Route::post('/companies', [CompanyController::class, 'create']);
    Route::get('/companies', [CompanyController::class, 'getAll']);
    Route::get('/companies/search', [CompanyController::class, 'search']);
    Route::get('/companies/{id}', [CompanyController::class, 'getById']);
    Route::post('/companies/{id}', [CompanyController::class, 'update']);
    Route::delete('/companies/{id}', [CompanyController::class, 'softDelete']);

    // Projects
    Route::post('/projects', [ProjectController::class, 'create']);
    Route::get('/projects', [ProjectController::class, 'getAll']);
    Route::get('/projects/search', [ProjectController::class, 'search']);
    Route::get('/projects/{id}', [ProjectController::class, 'getById']);
    Route::patch('/projects/{id}', [ProjectController::class, 'update']);
    Route::delete('/projects/{id}', [ProjectController::class, 'softDelete']);

    // Teams
    Route::post('/teams', [TeamController::class, 'create']);
    Route::get('/teams', [TeamController::class, 'getAll']);
    Route::get('/teams/search', [TeamController::class, 'search']);
    Route::get('/teams/{id}', [TeamController::class, 'getById']);
    Route::patch('/teams/{id}', [TeamController::class, 'update']);
    Route::delete('/teams/{id}', [TeamController::class, 'softDelete']);

    // Admin Doc Category
    Route::post('/admin-doc-categories', [AdminDocCategoryController::class, 'create']);
    Route::get('/admin-doc-categories', [AdminDocCategoryController::class, 'getAll']);
    Route::get('/admin-doc-categories/search', [AdminDocCategoryController::class, 'search']);
    Route::get('/admin-doc-categories/{id}', [AdminDocCategoryController::class, 'getById']);
    Route::patch('/admin-doc-categories/{id}', [AdminDocCategoryController::class, 'update']);
    Route::delete('/admin-doc-categories/{id}', [AdminDocCategoryController::class, 'softDelete']);

    // Admin Docs
    Route::post('/admin-docs', [AdminDocController::class, 'create']);
    Route::get('/admin-docs', [AdminDocController::class, 'getAll']);
    Route::get('/admin-docs/search', [AdminDocController::class, 'search']);
    Route::get('/admin-docs/{id}', [AdminDocController::class, 'getById']);
    Route::delete('/admin-docs/{id}', [AdminDocController::class, 'softDelete']);

    // Activity
    Route::post('/activities', [ActivityController::class, 'create']);
    Route::get('/activities', [ActivityController::class, 'getAll']);
    Route::get('/activities/search', [ActivityController::class, 'search']);
    Route::get('/activities/{id}', [ActivityController::class, 'getById']);
    Route::patch('/activities/{id}', [ActivityController::class, 'update']);
    Route::delete('/activities/{id}', [ActivityController::class, 'softDelete']);

    // Activity Doc Category
    Route::post('/activity-doc-categories', [ActivityDocCategoryController::class, 'create']);
    Route::get('/activity-doc-categories', [ActivityDocCategoryController::class, 'getAll']);
    Route::get('/activity-doc-categories/search', [ActivityDocCategoryController::class, 'search']);
    Route::get('/activity-doc-categories/{id}', [ActivityDocCategoryController::class, 'getById']);
    Route::patch('/activity-doc-categories/{id}', [ActivityDocCategoryController::class, 'update']);
    Route::delete('/activity-doc-categories/{id}', [ActivityDocCategoryController::class, 'softDelete']);

    // Activity Docs
    Route::post('/activity-docs', [ActivityDocController::class, 'create']);
    Route::get('/activity-docs', [ActivityDocController::class, 'getAll']);
    Route::get('/activity-docs/search', [ActivityDocController::class, 'search']);
    Route::get('/activity-docs/{id}', [ActivityDocController::class, 'getById']);
    Route::delete('/activity-docs/{id}', [ActivityDocController::class, 'softDelete']);
});
