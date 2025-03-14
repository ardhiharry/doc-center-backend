<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Http\Requests\ActivityCreateRequest;
use App\Http\Requests\ActivityUpdateRequest;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function create(ActivityCreateRequest $request)
    {
        $activity = Activity::where('title', $request->title)->exists();

        if ($activity) {
            return Response::handler(
                400,
                'Failed to create activity',
                [],
                'Activity title already exists.'
            );
        }

        $activity = Activity::create($request->all());

        return Response::handler(
            200,
            'Activity created successfully',
            ActivityResource::make($activity)
        );
    }

    public function getAll()
    {
        $activities = Activity::withoutTrashed()->get();

        if ($activities->isEmpty()) {
            return Response::handler(
                200,
                'Activities retrieved successfully'
            );
        }

        return Response::handler(
            200,
            'Activities retrieved successfully',
            ActivityResource::collection($activities)
        );
    }

    public function getById($id)
    {
        $activity = Activity::find($id);

        if (!$activity) {
            return Response::handler(
                400,
                'Failed to retrieve activity',
                [],
                'Activity not found.'
            );
        }

        return Response::handler(
            200,
            'Activity retrieved successfully',
            [$activity]
        );
    }

    public function update(ActivityUpdateRequest $request, $id)
    {
        $activity = Activity::find($id);

        if (!$activity) {
            return Response::handler(
                400,
                'Failed to update activity',
                [],
                'Activity not found.'
            );
        }

        $activity->update($request->only([
            'title',
            'start_date',
            'end_date',
            'project_id'
        ]));

        return Response::handler(
            200,
            'Activity updated successfully',
            ActivityResource::make($activity)
        );
    }

    public function softDelete($id)
    {
        $activity = Activity::find($id);

        if (!$activity) {
            return Response::handler(
                400,
                'Failed to delete activity',
                [],
                'Activity not found.'
            );
        }

        $activity->delete();

        return Response::handler(
            200,
            'Activity deleted successfully'
        );
    }
}
