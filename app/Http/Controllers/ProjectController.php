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
        try {
            $project = Project::where('name', $request->name)->exists();

            if ($project) {
                return Response::handler(
                    400,
                    'Failed to create project',
                    [],
                    [],
                    ['name' => ['Project name already exists.']]
                );
            }

            $project = Project::create($request->all());

            $project->load('company');

            return Response::handler(
                201,
                'Project created successfully',
                ProjectResource::make($project)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to create project',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function getAll(Request $request): JsonResponse
    {
        try {
            $projects = Project::with('company')
                ->withoutTrashed()
                ->orderBy('name', 'asc')
                ->paginate($request->query('limit', 10));

            if ($projects->isEmpty()) {
                return Response::handler(
                    200,
                    'Projects retrieved successfully'
                );
            }

            return Response::handler(
                200,
                'Projects retrieved successfully',
                ProjectResource::collection($projects),
                Response::pagination($projects)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to retrieve projects',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $query = Project::with('company');

            foreach ($request->all() as $key => $value) {
                if (in_array($key, ['name', 'company_id', 'start_date', 'end_date'])) {
                    $query->where($key, 'LIKE', "%{$value}%");
                }

                if ($key === 'id') {
                    $ids = is_array($value) ? $value : explode(',', $value);
                    $ids = array_map('trim', $ids);

                    $query->whereIn('id', $ids);
                }
            }

            $projects = $query->withoutTrashed()
                ->orderBy('name', 'asc')
                ->paginate($request->query('limit', 10));

            if ($projects->isEmpty()) {
                return Response::handler(
                    200,
                    'Projects retrieved successfully'
                );
            }

            return Response::handler(
                200,
                'Projects retrieved successfully',
                ProjectResource::collection($projects),
                Response::pagination($projects)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to retrieve projects',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function getById($id): JsonResponse
    {
        try {
            $project = Project::with('company')->find($id);

            if (!$project) {
                return Response::handler(
                    400,
                    'Failed to retrieve project',
                    [],
                    [],
                    'Project not found.'
                );
            }

            return Response::handler(
                200,
                'Project retrieved successfully',
                [ProjectResource::make($project)]
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to retrieve project',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function update(ProjectUpdateRequest $request, $id): JsonResponse
    {
        try {
            $project = Project::find($id);

            if (!$project) {
                return Response::handler(
                    400,
                    'Failed to update project',
                    [],
                    [],
                    'Project not found.'
                );
            }

            if ($request->name !== $project->name) {
                if (Project::where('name', $request->name)
                    ->where('id', '!=', $project->id)
                    ->exists()
                ) {
                    return Response::handler(
                        400,
                        'Failed to update project',
                        [],
                        [],
                        ['name' => ['Project name already exists.']]
                    );
                }
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
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to update project',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function softDelete($id): JsonResponse
    {
        try {
            $project = Project::withoutTrashed()->find($id);

            if (!$project) {
                return Response::handler(
                    400,
                    'Failed to delete project',
                    [],
                    [],
                    'Project not found.'
                );
            }

            $project->delete();

            return Response::handler(
                200,
                'Project deleted successfully'
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to delete project',
                [],
                [],
                $err->getMessage()
            );
        }
    }
}
