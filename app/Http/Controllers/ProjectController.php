<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\ProjectCreateRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function create(ProjectCreateRequest $request)
    {
        $project = Project::where('project_name', $request->project_name)->exists();

        if ($project) {
            return ResponseHelper::error(
                400,
                'Failed to create project',
                ['Project name already exists.']
            );
        }

        $project = Project::create($request->all());

        return ResponseHelper::success(
            201,
            'Project created successfully',
            ProjectResource::make($project)
        );
    }

    public function getAll()
    {
        $projects = Project::withoutTrashed()->get();

        if ($projects->isEmpty()) {
            return ResponseHelper::error(
                400,
                'Failed to retrieve projects',
                ['There are no projects yet.']
            );
        }

        return ResponseHelper::success(
            200,
            'Projects retrieved successfully',
            $projects
        );
    }

    public function getById($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return ResponseHelper::error(
                400,
                'Failed to retrieve project',
                ['Project not found.']
            );
        }

        return ResponseHelper::success(
            200,
            'Project retrieved successfully',
            $project
        );
    }

    public function update(Request $request, $id)
    {
        $project = Project::find($id);

        if (!$project) {
            return ResponseHelper::error(
                400,
                'Failed to update project',
                ['Project not found.']
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

        return ResponseHelper::success(
            200,
            'Project updated successfully',
            $project
        );
    }

    public function softDelete($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return ResponseHelper::error(
                400,
                'Failed to delete project',
                ['Project not found.']
            );
        }

        $project->delete();

        return ResponseHelper::success(
            200,
            'Project deleted successfully'
        );
    }
}
