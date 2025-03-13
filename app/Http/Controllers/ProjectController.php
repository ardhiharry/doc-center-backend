<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Http\Requests\ProjectCreateRequest;
use App\Http\Requests\ProjectUpdateRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;

class ProjectController extends Controller
{
    public function create(ProjectCreateRequest $request)
    {
        $project = Project::where('project_name', $request->project_name)->exists();

        if ($project) {
            return Response::handler(
                400,
                'Failed to create project',
                [],
                ['project_name' => ['Project name already exists.']]
            );
        }

        $project = Project::create($request->all());

        return Response::handler(
            201,
            'Project created successfully',
            ProjectResource::make($project)
        );
    }

    public function getAll()
    {
        $projects = Project::withoutTrashed()->get();

        if ($projects->isEmpty()) {
            return Response::handler(
                200,
                'Projects retrieved successfully',
            );
        }

        return Response::handler(
            200,
            'Projects retrieved successfully',
            $projects
        );
    }

    public function getById($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return Response::handler(
                400,
                'Failed to retrieve project',
                [],
                ['project' => ['Project not found.']]
            );
        }

        return Response::handler(
            200,
            'Project retrieved successfully',
            $project
        );
    }

    public function update(ProjectUpdateRequest $request, $id)
    {
        $project = Project::find($id);

        if (!$project) {
            return Response::handler(
                400,
                'Failed to update project',
                [],
                ['project' => ['Project not found.']]
            );
        }

        $project->update($request->only([
            'project_name',
            'company_name',
            'company_address',
            'director_name',
            'director_phone',
            'start_date',
            'end_date'
        ]));

        return Response::handler(
            200,
            'Project updated successfully',
            $project
        );
    }

    public function softDelete($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return Response::handler(
                400,
                'Failed to delete project',
                [],
                ['project' => ['Project not found.']]
            );
        }

        $project->delete();

        return Response::handler(
            200,
            'Project deleted successfully'
        );
    }
}
