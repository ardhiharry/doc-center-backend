<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\AdminDocCategoryCreateRequest;
use App\Http\Requests\AdminDocCategoryUpdateRequest;
use App\Http\Resources\AdminDocCategoryResource;
use App\Models\AdminDocCategory;
use Illuminate\Http\Request;

class AdminDocCategoryController extends Controller
{
    public function create(AdminDocCategoryCreateRequest $request)
    {
        $adminDocCategory = AdminDocCategory::where('name', $request->name)->exists();

        if ($adminDocCategory) {
            return ResponseHelper::error(
                400,
                'Failed to create admin doc category',
                ['Admin doc category name already exists.']
            );
        }

        $adminDocCategory = AdminDocCategory::create($request->all());

        return ResponseHelper::success(
            201,
            'Admin doc category created successfully',
            AdminDocCategoryResource::make($adminDocCategory)
        );
    }

    public function getAll()
    {
        $adminDocCategories = AdminDocCategory::withoutTrashed()->get();

        if ($adminDocCategories->isEmpty()) {
            return ResponseHelper::success(
                204,
                'Admin doc categories retrieved successfully',
            );
        }

        return ResponseHelper::success(
            200,
            'Admin doc categories retrieved successfully',
            $adminDocCategories
        );
    }

    public function getById($id)
    {
        $adminDocCategory = AdminDocCategory::find($id);

        if (!$adminDocCategory) {
            return ResponseHelper::error(
                400,
                'Failed to retrieve admin doc category',
                ['Admin doc category not found.']
            );
        }

        return ResponseHelper::success(
            200,
            'Admin doc category retrieved successfully',
            $adminDocCategory
        );
    }

    public function update(AdminDocCategoryUpdateRequest $request, $id)
    {
        $adminDocCategory = AdminDocCategory::find($id);

        if (!$adminDocCategory) {
            return ResponseHelper::error(
                400,
                'Failed to update admin doc category',
                ['Admin doc category not found.']
            );
        }

        $adminDocCategory->update($request->only([
            'name',
        ]));

        return ResponseHelper::success(
            200,
            'Admin doc category updated successfully',
            $adminDocCategory
        );
    }

    public function softDelete($id)
    {
        $adminDocCategory = AdminDocCategory::find($id);

        if (!$adminDocCategory) {
            return ResponseHelper::error(
                400,
                'Failed to delete admin doc category',
                ['Admin doc category not found.']
            );
        }

        $adminDocCategory->delete();

        return ResponseHelper::success(
            200,
            'Admin doc category deleted successfully'
        );
    }
}
