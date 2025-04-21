<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Http\Requests\ActivityDocCategoryCreateRequest;
use App\Http\Requests\ActivityDocCategoryUpdateRequest;
use App\Http\Resources\ActivityDocCategoryResource;
use App\Models\ActivityDocCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityDocCategoryController extends Controller
{
    public function create(ActivityDocCategoryCreateRequest $request): JsonResponse
    {
        try {
            $activityDocCategory = ActivityDocCategory::where('name', $request->name)->exists();

            if ($activityDocCategory) {
                return Response::handler(
                    400,
                    'Failed to create activity doc category',
                    [],
                    [],
                    ['name' => ['Activity doc category name already exists.']]
                );
            }

            $activityDocCategory = ActivityDocCategory::create($request->all());

            return Response::handler(
                201,
                'Activity doc category created successfully',
                ActivityDocCategoryResource::make($activityDocCategory)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to create activity doc category',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function getAll(Request $request): JsonResponse
    {
        try {
            $activityDocCategories = ActivityDocCategory::withoutTrashed()
                ->paginate($request->query('limit', 10));

            if ($activityDocCategories->isEmpty()) {
                return Response::handler(
                    200,
                    'Activity doc categories retrieved successfully'
                );
            }

            return Response::handler(
                200,
                'Activity doc categories retrieved successfully',
                ActivityDocCategoryResource::collection($activityDocCategories),
                Response::pagination($activityDocCategories)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to retrieve activity doc categories',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $query = ActivityDocCategory::withoutTrashed();

            foreach ($request->all() as $key => $value) {
                if (in_array($key, ['name'])) {
                    $query->where($key, 'LIKE', "%{$value}%");
                }
            }

            $activityDocCategories = $query->paginate($request->query('limit', 10));

            if ($activityDocCategories->isEmpty()) {
                return Response::handler(
                    200,
                    'Activity doc categories retrieved successfully'
                );
            }

            return Response::handler(
                200,
                'Activity doc categories retrieved successfully',
                ActivityDocCategoryResource::collection($activityDocCategories),
                Response::pagination($activityDocCategories)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to retrieve activity doc categories',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function getById($id): JsonResponse
    {
        try {
            $activityDocCategory = ActivityDocCategory::find($id);

            if (!$activityDocCategory) {
                return Response::handler(
                    400,
                    'Failed to retrieve activity doc category',
                    [],
                    [],
                    'Activity doc category not found.'
                );
            }

            return Response::handler(
                200,
                'Activity doc category retrieved successfully',
                [ActivityDocCategoryResource::make($activityDocCategory)]
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to retrieve activity doc category',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function update(ActivityDocCategoryUpdateRequest $request, $id): JsonResponse
    {
        try {
            $activityDocCategory = ActivityDocCategory::find($id);

            if (!$activityDocCategory) {
                return Response::handler(
                    400,
                    'Failed to update activity doc category',
                    [],
                    [],
                    'Activity doc category not found.'
                );
            }

            if ($request->name !== $activityDocCategory->name) {
                if (ActivityDocCategory::where('name', $request->name)
                    ->where('id', '!=', $id)
                    ->exists()
                ) {
                    return Response::handler(
                        400,
                        'Failed to update activity doc category',
                        [],
                        [],
                        ['name' => ['Activity doc category name already exists.']]
                    );
                }
            }

            $activityDocCategory->update($request->only([
                'name',
            ]));

            return Response::handler(
                200,
                'Activity doc category updated successfully',
                ActivityDocCategoryResource::make($activityDocCategory)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to update activity doc category',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function softDelete($id)
    {
        try {
            $activityDocCategory = ActivityDocCategory::find($id);

            if (!$activityDocCategory) {
                return Response::handler(
                    400,
                    'Failed to delete activity doc category',
                    [],
                    [],
                    'Activity doc category not found.'
                );
            }

            $activityDocCategory->delete();

            return Response::handler(
                200,
                'Activity doc category deleted successfully'
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to delete activity doc category',
                [],
                [],
                $err->getMessage()
            );
        }
    }
}
