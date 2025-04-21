<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Http\Requests\AdminDocCategoryCreateRequest;
use App\Http\Requests\AdminDocCategoryUpdateRequest;
use App\Http\Resources\AdminDocCategoryResource;
use App\Models\AdminDocCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminDocCategoryController extends Controller
{
    public function create(AdminDocCategoryCreateRequest $request): JsonResponse
    {
        try {
            $adminDocCategory = AdminDocCategory::where('name', $request->name)->exists();

            if ($adminDocCategory) {
                return Response::handler(
                    400,
                    'Failed to create admin doc category',
                    [],
                    [],
                    ['name' => ['Admin doc category name already exists.']]
                );
            }

            $adminDocCategory = AdminDocCategory::create($request->all());

            return Response::handler(
                201,
                'Admin doc category created successfully',
                AdminDocCategoryResource::make($adminDocCategory)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to create admin doc category',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function getAll(Request $request): JsonResponse
    {
        try {
            $adminDocCategories = AdminDocCategory::withoutTrashed()
                ->paginate(request()->query('limit', 10));

            if ($adminDocCategories->isEmpty()) {
                return Response::handler(
                    200,
                    'Admin doc categories retrieved successfully'
                );
            }

            return Response::handler(
                200,
                'Admin doc categories retrieved successfully',
                AdminDocCategoryResource::collection($adminDocCategories),
                Response::pagination($adminDocCategories)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to retrieve admin doc categories',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $query = AdminDocCategory::withoutTrashed();

            foreach ($request->all() as $key => $value) {
                if (in_array($key, ['name'])) {
                    $query->where($key, 'LIKE', "%{$value}%");
                }
            }

            $adminDocCategories = $query->paginate($request->query('limit', 10));

            if ($adminDocCategories->isEmpty()) {
                return Response::handler(
                    200,
                    'Admin doc categories retrieved successfully'
                );
            }

            return Response::handler(
                200,
                'Admin doc categories retrieved successfully',
                AdminDocCategoryResource::collection($adminDocCategories),
                Response::pagination($adminDocCategories)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to retrieve admin doc categories',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function getById($id): JsonResponse
    {
        try {
            $adminDocCategory = AdminDocCategory::find($id);

            if (!$adminDocCategory) {
                return Response::handler(
                    400,
                    'Failed to retrieve admin doc category',
                    [],
                    [],
                    'Admin doc category not found.'
                );
            }

            return Response::handler(
                200,
                'Admin doc category retrieved successfully',
                [AdminDocCategoryResource::make($adminDocCategory)]
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to retrieve admin doc category',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function update(AdminDocCategoryUpdateRequest $request, $id): JsonResponse
    {
        try {
            $adminDocCategory = AdminDocCategory::find($id);

            if (!$adminDocCategory) {
                return Response::handler(
                    400,
                    'Failed to update admin doc category',
                    [],
                    [],
                    'Admin doc category not found.'
                );
            }

            if ($request->name !== $adminDocCategory->name) {
                if (AdminDocCategory::where('name', $request->name)
                    ->where('id', '!=', $adminDocCategory->id)
                    ->exists()
                ) {
                    return Response::handler(
                        400,
                        'Failed to update admin doc category',
                        [],
                        [],
                        ['name' => ['Admin doc category name already exists.']]
                    );
                }
            }

            if (AdminDocCategory::where('name', $request->name)->exists()) {
                return Response::handler(
                    400,
                    'Failed to update admin doc category',
                    [],
                    [],
                    ['name' => ['Admin doc category name already exists.']]
                );
            }

            $adminDocCategory->update($request->only([
                'name',
            ]));

            return Response::handler(
                200,
                'Admin doc category updated successfully',
                AdminDocCategoryResource::make($adminDocCategory)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to update admin doc category',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function softDelete($id): JsonResponse
    {
        try {
            $adminDocCategory = AdminDocCategory::find($id);

            if (!$adminDocCategory) {
                return Response::handler(
                    400,
                    'Failed to delete admin doc category',
                    [],
                    [],
                    'Admin doc category not found.'
                );
            }

            $adminDocCategory->delete();

            return Response::handler(
                200,
                'Admin doc category deleted successfully'
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to delete admin doc category',
                [],
                [],
                $err->getMessage()
            );
        }
    }
}
