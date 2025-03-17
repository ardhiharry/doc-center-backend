<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Http\Requests\AdminDocRequest;
use App\Http\Resources\AdminDocResource;
use App\Models\AdminDoc;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminDocController extends Controller
{
    public function create(AdminDocRequest $request): JsonResponse
    {
        $adminDoc = AdminDoc::where('title', $request->title)->exists();

        if ($adminDoc) {
            return Response::handler(
                400,
                'Failed to create admin doc',
                [],
                'Admin doc title already exists.'
            );
        }

        $filePath = null;

        if ($request->hasFile('file')) {
            $date = Carbon::now()->format('Ymd');
            $uuid = Str::uuid()->toString();
            $randomStr = substr(str_replace('-', '', $uuid), 0, 27);
            $fileName = "{$date}-{$randomStr}.{$request->file('file')->extension()}";

            $filePath = $request->file('file')->storeAs('admin_docs', $fileName, 'public');
        }

        $adminDoc = AdminDoc::create([
            'title' => $request->title,
            'file' => $filePath,
            'project_id' => $request->project_id,
            'admin_doc_category_id' => $request->admin_doc_category_id
        ]);

        return Response::handler(
            200,
            'Admin doc created successfully',
            AdminDocResource::make($adminDoc)
        );
    }

    public function getAll(): JsonResponse
    {
        $adminDocs = AdminDoc::withoutTrashed()->get();

        if ($adminDocs->isEmpty()) {
            return Response::handler(
                200,
                'Admin docs retrieved successfully'
            );
        }

        return Response::handler(
            200,
            'Admin docs retrieved successfully',
            $adminDocs
        );
    }

    public function search(Request $request): JsonResponse
    {
        $query = AdminDoc::query();

        foreach ($request->all() as $key => $value) {
            if (in_array($key, ['title', 'project_id', 'admin_doc_category_id'])) {
                $query->where($key, 'LIKE', "%{$value}%");
            }
        }

        $adminDocs = $query->withoutTrashed()->get();

        if ($adminDocs->isEmpty()) {
            return Response::handler(
                200,
                'Admin docs retrieved successfully'
            );
        }

        return Response::handler(
            200,
            'Admin docs retrieved successfully',
            $adminDocs
        );
    }

    public function getById($id): JsonResponse
    {
        $adminDoc = AdminDoc::find($id);

        if (!$adminDoc) {
            return Response::handler(
                400,
                'Failed to retrieve admin doc',
                [],
                'Admin doc not found.'
            );
        }

        return Response::handler(
            200,
            'Admin doc retrieved successfully',
            [$adminDoc]
        );
    }

    public function softDelete($id): JsonResponse
    {
        $adminDoc = AdminDoc::find($id);

        if (!$adminDoc) {
            return Response::handler(
                400,
                'Failed to delete admin doc',
                [],
                'Admin doc not found.'
            );
        }

        $adminDoc->delete();

        return Response::handler(
            200,
            'Admin doc deleted successfully'
        );
    }
}
