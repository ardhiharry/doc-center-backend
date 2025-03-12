<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\ActivityCreateRequest;
use App\Http\Requests\ActivityUpdateRequest;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function create(ActivityCreateRequest $request)
    {
        $activity = Activity::where('id', $request->id)->exists();

        if ($activity) {
            return ResponseHelper::error(
                400,
                'Failed to create activity',
                ['Activity not found.']
            );
        }

        $activity = Activity::create($request->all());

        return ResponseHelper::success(
            200,
            'Activity created successfully',
            ActivityResource::make($activity)
        );
    }

    public function getAll()
    {
        $activities = Activity::withoutTrashed()->get();

        if ($activities->isEmpty()) {
            return ResponseHelper::success(
                204,
                'Activities retrieved successfully',
            );
        }

        return ResponseHelper::success(
            200,
            'Activities retrieved successfully',
            ActivityResource::collection($activities)
        );
    }

    public function getById($id)
    {
        $activity = Activity::find($id);

        if (!$activity) {
            return ResponseHelper::error(
                400,
                'Failed to retrieve activity',
                ['Activity not found.']
            );
        }

        return ResponseHelper::success(
            200,
            'Activity retrieved successfully',
            ActivityResource::make($activity)
        );
    }

    public function update(ActivityUpdateRequest $request, $id)
    {
        $activity = Activity::find($id);

        if (!$activity) {
            return ResponseHelper::error(
                400,
                'Failed to update activity',
                ['Activity not found.']
            );
        }

        $activity->update($request->only([
            'title',
            'start_date',
            'end_date',
            'project_id'
        ]));

        return ResponseHelper::success(
            200,
            'Activity updated successfully',
            ActivityResource::make($activity)
        );
    }

    public function softDelete($id)
    {
        $activity = Activity::find($id);

        if (!$activity) {
            return ResponseHelper::error(
                400,
                'Failed to delete activity',
                ['Activity not found.']
            );
        }

        $activity->delete();

        return ResponseHelper::success(
            200,
            'Activity deleted successfully'
        );
    }
}
