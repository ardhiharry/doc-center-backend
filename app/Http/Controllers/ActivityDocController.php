<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\ActivityDocRequest;
use App\Http\Resources\ActivityDocResource;
use App\Models\ActivityDoc;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ActivityDocController extends Controller
{
    public function create(ActivityDocRequest $request)
    {
        try {
            $activityDoc = ActivityDoc::where('title', $request->title)->exists();

            if ($activityDoc) {
                return ResponseHelper::error(
                    400,
                    'Failed to create activity doc',
                    ['Activity doc title already exists.']
                );
            }

            $date = Carbon::now()->format('Ymd');
            $uuid = Str::uuid()->toString();
            $randomStr = substr(str_replace('-', '', $uuid), 0, 27);
            $fileName = "{$date}-{$randomStr}.pdf";

            $filePath = $request->file('file')->storeAs('activity_docs', $fileName, 'public');

            $activityDoc = ActivityDoc::create([
                'title' => $request->title,
                'file' => $filePath,
                'description' => $request->description,
                'tags' => $request->tags,
                'activity_doc_category_id' => $request->activity_doc_category_id,
                'activity_id' => $request->activity_id
            ]);

            return ResponseHelper::success(
                200,
                'Activity doc created successfully',
                ActivityDocResource::make($activityDoc)
            );
        } catch (PostTooLargeException $err) {
            return ResponseHelper::error(
                400,
                'Failed to create activity document',
                ['Uploaded file exceeds the size limit.']
            );
        }
    }

    public function getAll()
    {
        $activityDocs = ActivityDoc::withoutTrashed()->get();

        if ($activityDocs->isEmpty()) {
            return ResponseHelper::success(
                204
            );
        }

        return ResponseHelper::success(
            200,
            'Activity docs retrieved successfully',
            ActivityDocResource::collection($activityDocs)
        );
    }

    public function getById($id)
    {
        $activityDoc = ActivityDoc::find($id);

        if (!$activityDoc) {
            return ResponseHelper::error(
                400,
                'Failed to retrieve activity doc',
                ['Activity doc not found.']
            );
        }

        return ResponseHelper::success(
            200,
            'Activity doc retrieved successfully',
            ActivityDocResource::make($activityDoc)
        );
    }

    public function softDelete($id)
    {
        $activityDoc = ActivityDoc::find($id);

        if (!$activityDoc) {
            return ResponseHelper::error(
                400,
                'Failed to delete activity doc',
                ['Activity doc not found.']
            );
        }

        $activityDoc->delete();

        return ResponseHelper::success(
            200,
            'Activity doc deleted successfully'
        );
    }
}
