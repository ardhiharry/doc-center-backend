<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Http\Requests\ActivityDocRequest;
use App\Http\Resources\ActivityDocResource;
use App\Models\ActivityDoc;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ActivityDocController extends Controller
{
    public function create(ActivityDocRequest $request): JsonResponse
    {
        try {
            $activityDoc = ActivityDoc::where('title', $request->title)->exists();

            if ($activityDoc) {
                return Response::handler(
                    400,
                    'Failed to create activity doc',
                    [],
                    'Activity doc title already exists.'
                );
            }

            $filePath = null;

            if ($request->hasFile('file')) {
                $date = Carbon::now()->format('Ymd');
                $uuid = Str::uuid()->toString();
                $randomStr = substr(str_replace('-', '', $uuid), 0, 27);
                $fileName = "{$date}-{$randomStr}.{$request->file('file')->extension()}";

                $filePath = $request->file('file')->storeAs('activity_docs', $fileName, 'public');
            }

            $activityDoc = ActivityDoc::create([
                'title' => $request->title,
                'file' => $filePath,
                'description' => $request->description,
                'tags' => $request->tags,
                'activity_doc_category_id' => $request->activity_doc_category_id,
                'activity_id' => $request->activity_id
            ]);

            return Response::handler(
                200,
                'Activity doc created successfully',
                ActivityDocResource::make($activityDoc)
            );
        } catch (PostTooLargeException $err) {
            return Response::handler(
                400,
                'Failed to create activity document',
                [],
                'Uploaded file exceeds the size limit.'
            );
        }
    }

    public function getAll(): JsonResponse
    {
        $activityDocs = ActivityDoc::withoutTrashed()->get();

        if ($activityDocs->isEmpty()) {
            return Response::handler(
                200,
                'Activity docs retrieved successfully'
            );
        }

        return Response::handler(
            200,
            'Activity docs retrieved successfully',
            ActivityDocResource::collection($activityDocs)
        );
    }

    public function search(Request $request): JsonResponse
    {
        $query = ActivityDoc::query();

        foreach ($request->all() as $key => $value) {
            if (in_array($key, ['id', 'title', 'description', 'activity_doc_category_id', 'activity_id'])) {
                $query->where($key, 'LIKE', "%{$value}%");
            }

            if ($key === 'tags') {
                $tags = is_array($value) ? $value : explode(',', $value);

                foreach ($tags as $tag) {
                    $query->orWhereJsonContains('tags', $tag);
                }
            }
        }

        $activityDocs = $query->withoutTrashed()->get();

        if ($activityDocs->isEmpty()) {
            return Response::handler(
                200,
                'Activity docs retrieved successfully'
            );
        }

        return Response::handler(
            200,
            'Activity docs retrieved successfully',
            ActivityDocResource::collection($activityDocs)
        );
    }

    public function getById($id): JsonResponse
    {
        $activityDoc = ActivityDoc::find($id);

        if (!$activityDoc) {
            return Response::handler(
                400,
                'Failed to retrieve activity doc',
                [],
                'Activity doc not found.'
            );
        }

        return Response::handler(
            200,
            'Activity doc retrieved successfully',
            [$activityDoc]
        );
    }

    public function softDelete($id): JsonResponse
    {
        $activityDoc = ActivityDoc::find($id);

        if (!$activityDoc) {
            return Response::handler(
                400,
                'Failed to delete activity doc',
                [],
                'Activity doc not found.'
            );
        }

        $activityDoc->delete();

        return Response::handler(
            200,
            'Activity doc deleted successfully'
        );
    }
}
