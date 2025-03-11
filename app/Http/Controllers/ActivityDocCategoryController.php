<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\ActivityDocCategoryCreateRequest;
use App\Http\Requests\ActivityDocCategoryUpdateRequest;
use App\Http\Resources\ActivityDocCategoryResource;
use App\Models\ActivityDocCategory;
use Illuminate\Http\Request;

class ActivityDocCategoryController extends Controller
{
    public function create(ActivityDocCategoryCreateRequest $request)
    {
        $activityDocCategory = ActivityDocCategory::where('name', $request->name)->exists();

        if ($activityDocCategory) {
            return ResponseHelper::error(
                400,
                'Failed to create activity doc category',
                ['Activity doc category name already exists.']
            );
        }

        $activityDocCategory = ActivityDocCategory::create($request->all());

        return ResponseHelper::success(
            201,
            'Activity doc category created successfully',
            ActivityDocCategoryResource::make($activityDocCategory)
        );
    }

    public function getAll()
    {
        $activityDocCategories = ActivityDocCategory::withoutTrashed()->get();

        if ($activityDocCategories->isEmpty()) {
            return ResponseHelper::error(
                400,
                'Failed to retrieve activity doc categories',
                ['There are no activity doc categories yet.']
            );
        }

        return ResponseHelper::success(
            200,
            'Activity doc categories retrieved successfully',
            $activityDocCategories
        );
    }

    public function getById($id)
    {
        $activityDocCategory = ActivityDocCategory::find($id);

        if (!$activityDocCategory) {
            return ResponseHelper::error(
                400,
                'Failed to retrieve activity doc category',
                ['Activity doc category not found.']
            );
        }

        return ResponseHelper::success(
            200,
            'Activity doc category retrieved successfully',
            $activityDocCategory
        );
    }

    public function update(ActivityDocCategoryUpdateRequest $request, $id)
    {
        $activityDocCategory = ActivityDocCategory::find($id);

        if (!$activityDocCategory) {
            return ResponseHelper::error(
                400,
                'Failed to update activity doc category',
                ['Activity doc category not found.']
            );
        }

        $activityDocCategory->update($request->only([
            'name',
        ]));

        return ResponseHelper::success(
            200,
            'Activity doc category updated successfully',
            $activityDocCategory
        );
    }

    public function softDelete($id)
    {
        $activityDocCategory = ActivityDocCategory::find($id);

        if (!$activityDocCategory) {
            return ResponseHelper::error(
                400,
                'Failed to delete activity doc category',
                ['Activity doc category not found.']
            );
        }

        $activityDocCategory->delete();

        return ResponseHelper::success(
            200,
            'Activity doc category deleted successfully'
        );
    }
}
