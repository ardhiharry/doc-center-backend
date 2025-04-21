<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Http\Requests\TeamCreateRequest;
use App\Http\Requests\TeamUpdateRequest;
use App\Http\Resources\TeamResource;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function create(TeamCreateRequest $request): JsonResponse
    {
        try {
            $team = Team::create($request->all());

            $team->load('project');
            $team->load('user');

            return Response::handler(
                201,
                'Team created successfully',
                TeamResource::make($team)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to create team',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function getAll(Request $request): JsonResponse
    {
        try {
            $teams = Team::with(['project', 'user'])
                ->withoutTrashed()
                ->paginate($request->query('limit', 10));

            if ($teams->isEmpty()) {
                return Response::handler(
                    200,
                    'Teams retrieved successfully'
                );
            }

            return Response::handler(
                200,
                'Teams retrieved successfully',
                TeamResource::collection($teams),
                Response::pagination($teams)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to retrieve teams',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $query = Team::with(['project', 'user']);

            $relationList = [
                'project_name' => ['relation' => 'project', 'column' => 'name'],
                'user_username' => ['relation' => 'user', 'column' => 'username'],
                'user_name' => ['relation' => 'user', 'column' => 'name'],
            ];

            foreach ($request->all() as $key => $value) {
                if (array_key_exists($key, $relationList)) {
                    $relation = $relationList[$key]['relation'];
                    $column = $relationList[$key]['column'];

                    $query->whereHas($relation, function ($q) use ($column, $value) {
                        $q->where($column, 'LIKE', "%{$value}%");
                    });

                    continue;
                }

                if ($key === 'project_id') {
                    $projectIds = is_array($value) ? $value : explode(',', $value);
                    $projectIds = array_map('trim', $projectIds);

                    $query->whereHas('project', function ($q) use ($projectIds) {
                        $q->whereIn('id', $projectIds);
                    });
                }

                if (in_array($key, ['user_id'])) {
                    $query->where($key, $value);
                }
            }

            $teams = $query->withoutTrashed()
                ->paginate($request->query('limit', 10));

            if ($teams->isEmpty()) {
                return Response::handler(
                    200,
                    'Teams retrieved successfully'
                );
            }

            return Response::handler(
                200,
                'Teams retrieved successfully',
                TeamResource::collection($teams),
                Response::pagination($teams)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to retrieve teams',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function getById($id): JsonResponse
    {
        try {
            $team = Team::with(['project', 'user'])->find($id);

            if (!$team) {
                return Response::handler(
                    400,
                    'Failed to retrieve team',
                    [],
                    [],
                    'Team not found.'
                );
            }

            return Response::handler(
                200,
                'Team retrieved successfully',
                [TeamResource::make($team)]
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to retrieve team',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function update(TeamUpdateRequest $request, $id): JsonResponse
    {
        try {
            $team = Team::find($id);

            if (!$team) {
                return Response::handler(
                    400,
                    'Failed to update team',
                    [],
                    [],
                    'Team not found.'
                );
            }

            $team->update($request->only([
                'project_id',
                'user_id',
            ]));

            $team->load('project');
            $team->load('user');

            return Response::handler(
                200,
                'Team updated successfully',
                [TeamResource::make($team)]
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to update team',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function softDelete($projectId): JsonResponse
    {
        try {
            $teams = Team::withoutTrashed()->where('project_id', $projectId)->get();

            if ($teams->isEmpty()) {
                return Response::handler(
                    400,
                    'Failed to delete teams',
                    [],
                    [],
                    'No teams found for this project.'
                );
            }

            Team::where('project_id', $projectId)->delete();

            return Response::handler(
                200,
                'Team deleted successfully'
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to delete team',
                [],
                [],
                $err->getMessage()
            );
        }
    }
}
