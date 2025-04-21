<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Http\Requests\ActivityCreateRequest;
use App\Http\Requests\ActivityUpdateRequest;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function create(ActivityCreateRequest $request): JsonResponse
    {
        try {
            $activity = Activity::where('title', $request->title)->exists();

            if ($activity) {
                return Response::handler(
                    400,
                    'Failed to create activity',
                    [],
                    [],
                    ['title' => ['Activity title already exists.']]
                );
            }

            $activity = Activity::create($request->all());

            $activity->load('project.company');

            return Response::handler(
                200,
                'Activity created successfully',
                ActivityResource::make($activity)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to create activity',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function getAll(Request $request): JsonResponse
    {
        try {
            $activities = Activity::with('project.company')
                ->withoutTrashed()
                ->paginate($request->query('limit', 10));

            if ($activities->isEmpty()) {
                return Response::handler(
                    200,
                    'Activities retrieved successfully'
                );
            }

            return Response::handler(
                200,
                'Activities retrieved successfully',
                ActivityResource::collection($activities),
                Response::pagination($activities)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to retrieve activities',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $query = Activity::with('project.company');

            foreach ($request->all() as $key => $value) {
                if (in_array($key, ['title', 'start_date', 'end_date'])) {
                    $query->where($key, 'LIKE', "%{$value}%");
                }

                if ($key === 'project_id') {
                    $projectIds = is_array($value) ? $value : explode(',', $value);
                    $projectIds = array_map('trim', $projectIds);

                    $query->whereHas('project', function ($q) use ($projectIds) {
                        $q->whereIn('id', $projectIds);
                    });
                }
            }

            $activities = $query->withoutTrashed()
                ->paginate($request->query('limit', 10));

            if ($activities->isEmpty()) {
                return Response::handler(
                    200,
                    'Activities retrieved successfully'
                );
            }

            return Response::handler(
                200,
                'Activities retrieved successfully',
                ActivityResource::collection($activities),
                Response::pagination($activities)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to retrieve activities',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function getById($id): JsonResponse
    {
        try {
            $activity = Activity::with('project.company')->find($id);

            if (!$activity) {
                return Response::handler(
                    400,
                    'Failed to retrieve activity',
                    [],
                    [],
                    'Activity not found.'
                );
            }

            return Response::handler(
                200,
                'Activity retrieved successfully',
                [ActivityResource::make($activity)]
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to retrieve activity',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function update(ActivityUpdateRequest $request, $id): JsonResponse
    {
        try {
            $activity = Activity::find($id);

            if (!$activity) {
                return Response::handler(
                    400,
                    'Failed to update activity',
                    [],
                    [],
                    'Activity not found.'
                );
            }

            if ($request->title !== $activity->title) {
                if (Activity::where('title', $request->title)
                    ->where('id', '!=', $id)
                    ->exists()
                ) {
                    return Response::handler(
                        400,
                        'Failed to update activity',
                        [],
                        [],
                        ['title' => ['Activity title already exists.']]
                    );
                }
            }

            $activity->update($request->only([
                'title',
                'start_date',
                'end_date',
                'project_id'
            ]));

            $activity->load('project.company');

            return Response::handler(
                200,
                'Activity updated successfully',
                ActivityResource::make($activity)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to update activity',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function softDelete($id): JsonResponse
    {
        try {
            $activity = Activity::find($id);

            if (!$activity) {
                return Response::handler(
                    400,
                    'Failed to delete activity',
                    [],
                    [],
                    'Activity not found.'
                );
            }

            $activity->delete();

            return Response::handler(
                200,
                'Activity deleted successfully'
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to delete activity',
                [],
                [],
                $err->getMessage()
            );
        }
    }
}
