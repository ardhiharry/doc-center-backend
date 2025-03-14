<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
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
            return Response::handler(
                400,
                'Failed to create admin doc category',
                [],
                'Admin doc category name already exists.'
            );
        }

        $adminDocCategory = AdminDocCategory::create($request->all());

        return Response::handler(
            201,
            'Admin doc category created successfully',
            AdminDocCategoryResource::make($adminDocCategory)
        );
    }

    public function getAll()
    {
        $adminDocCategories = AdminDocCategory::withoutTrashed()->get();

        if ($adminDocCategories->isEmpty()) {
            return Response::handler(
                200,
                'Admin doc categories retrieved successfully'
            );
        }

        return Response::handler(
            200,
            'Admin doc categories retrieved successfully',
            $adminDocCategories
        );
    }

    public function getById($id)
    {
        $adminDocCategory = AdminDocCategory::find($id);

        if (!$adminDocCategory) {
            return Response::handler(
                400,
                'Failed to retrieve admin doc category',
                [],
                'Admin doc category not found.'
            );
        }

        return Response::handler(
            200,
            'Admin doc category retrieved successfully',
            [$adminDocCategory]
        );
    }

    public function update(AdminDocCategoryUpdateRequest $request, $id)
    {
        $adminDocCategory = AdminDocCategory::find($id);

        if (!$adminDocCategory) {
            return Response::handler(
                400,
                'Failed to update admin doc category',
                [],
                'Admin doc category not found.'
            );
        }

        $adminDocCategory->update($request->only([
            'name',
        ]));

        return Response::handler(
            200,
            'Admin doc category updated successfully',
            $adminDocCategory
        );
    }

    public function softDelete($id)
    {
        $adminDocCategory = AdminDocCategory::find($id);

        if (!$adminDocCategory) {
            return Response::handler(
                400,
                'Failed to delete admin doc category',
                [],
                'Admin doc category not found.'
            );
        }

        $adminDocCategory->delete();

        return Response::handler(
            200,
            'Admin doc category deleted successfully'
        );
    }
}
