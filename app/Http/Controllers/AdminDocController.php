<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\AdminDocRequest;
use App\Http\Resources\AdminDocResource;
use App\Models\AdminDoc;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AdminDocController extends Controller
{
    public function create(AdminDocRequest $request)
    {
        $adminDoc = AdminDoc::where('title', $request->title)->exists();

        if ($adminDoc) {
            return ResponseHelper::error(
                400,
                'Failed to create admin doc',
                ['Admin doc title already exists.']
            );
        }

        $date = Carbon::now()->format('Ymd');
        $uuid = Str::uuid()->toString();
        $randomStr = substr(str_replace('-', '', $uuid), 0, 27);
        $fileName = "{$date}-{$randomStr}.pdf";

        $filePath = $request->file('file')->storeAs('admin_docs', $fileName, 'public');

        $adminDoc = AdminDoc::create([
            'title' => $request->title,
            'file' => $filePath,
            'project_id' => $request->project_id,
            'admin_doc_category_id' => $request->admin_doc_category_id
        ]);

        return ResponseHelper::success(
            200,
            'Admin doc created successfully',
            AdminDocResource::make($adminDoc)
        );
    }

    public function getAll()
    {
        $adminDocs = AdminDoc::withoutTrashed()->get();

        if ($adminDocs->isEmpty()) {
            return ResponseHelper::success(
                204,
                'Admin docs retrieved successfully',
            );
        }

        return ResponseHelper::success(
            200,
            'Admin docs retrieved successfully',
            AdminDocResource::collection($adminDocs)
        );
    }

    public function getById($id)
    {
        $adminDoc = AdminDoc::find($id);

        if (!$adminDoc) {
            return ResponseHelper::error(
                400,
                'Failed to retrieve admin doc',
                ['Admin doc not found.']
            );
        }

        return ResponseHelper::success(
            200,
            'Admin doc retrieved successfully',
            AdminDocResource::make($adminDoc)
        );
    }

    public function softDelete($id)
    {
        $adminDoc = AdminDoc::find($id);

        if (!$adminDoc) {
            return ResponseHelper::error(
                400,
                'Failed to delete admin doc',
                ['Admin doc not found.']
            );
        }

        $adminDoc->delete();

        return ResponseHelper::success(
            200,
            'Admin doc deleted successfully'
        );
    }
}
