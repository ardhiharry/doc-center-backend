<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Http\Requests\ProjectCreateRequest;
use App\Http\Requests\ProjectUpdateRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function create(ProjectCreateRequest $request): JsonResponse
    {
        $project = Project::where('name', $request->name)->exists();

        if ($project) {
            return Response::handler(
                400,
                'Failed to create project',
                [],
                'Project name already exists.'
            );
        }

        $project = Project::create($request->all());

        $project->load('company');

        return Response::handler(
            201,
            'Project created successfully',
            ProjectResource::make($project)
        );
    }

    public function getAll(): JsonResponse
    {
        $projects = Project::with('company')->withoutTrashed()->get();

        if ($projects->isEmpty()) {
            return Response::handler(
                200,
                'Projects retrieved successfully',
            );
        }

        return Response::handler(
            200,
            'Projects retrieved successfully',
            ProjectResource::collection($projects)
        );
    }

    public function search(Request $request): JsonResponse
    {
        $query = Project::with('company');

        foreach ($request->all() as $key => $value) {
            if (in_array($key, ['name', 'company_id', 'start_date', 'end_date'])) {
                $query->where($key, 'LIKE', "%{$value}%");
            }
        }

        $projects = $query->withoutTrashed()->get();

        if ($projects->isEmpty()) {
            return Response::handler(
                200,
                'Projects retrieved successfully'
            );
        }

        return Response::handler(
            200,
            'Projects retrieved successfully',
            ProjectResource::collection($projects)
        );
    }

    public function getById($id): JsonResponse
    {
        $project = Project::with('company')->find($id);

        if (!$project) {
            return Response::handler(
                400,
                'Failed to retrieve project',
                [],
                'Project not found.'
            );
        }

        return Response::handler(
            200,
            'Project retrieved successfully',
            [ProjectResource::make($project)]
        );
    }

    public function update(ProjectUpdateRequest $request, $id): JsonResponse
    {
        $project = Project::find($id);

        if (!$project) {
            return Response::handler(
                400,
                'Failed to update project',
                [],
                'Project not found.'
            );
        }

        $project->update($request->only([
            'name',
            'company_id',
            'start_date',
            'end_date'
        ]));

        $project->load('company');

        return Response::handler(
            200,
            'Project updated successfully',
            ProjectResource::make($project)
        );
    }

    public function softDelete($id): JsonResponse
    {
        $project = Project::find($id);

        if (!$project) {
            return Response::handler(
                400,
                'Failed to delete project',
                [],
                'Project not found.'
            );
        }

        $project->delete();

        return Response::handler(
            200,
            'Project deleted successfully'
        );
    }
}
