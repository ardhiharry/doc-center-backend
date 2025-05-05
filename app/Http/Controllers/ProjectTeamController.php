<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Http\Requests\ProjectTeamCreateRequest;
use App\Http\Requests\ProjectTeamUpdateRequest;
use App\Http\Resources\ProjectTeamResource;
use App\Models\ProjectTeam;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectTeamController extends Controller
{
    public function create(ProjectTeamCreateRequest $request): JsonResponse
    {
        try {
            $team = ProjectTeam::create($request->all());

            $team->load('project');
            $team->load('user');

            return Response::handler(
                201,
                'Berhasil membuat tim',
                ProjectTeamResource::make($team)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal membuat tim',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function getAll(Request $request): JsonResponse
    {
        try {
            $teams = ProjectTeam::with(['project', 'user'])
                ->withoutTrashed()
                ->whereHas('user')
                ->join('users', 'teams.user_id', '=', 'users.id')
                ->orderBy('users.name', 'asc')
                ->select('teams.*')
                ->paginate($request->query('limit', 10));

            if ($teams->isEmpty()) {
                return Response::handler(
                    200,
                    'Berhasil mengambil data tim'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data tim',
                ProjectTeamResource::collection($teams),
                Response::pagination($teams)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data tim',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $query = ProjectTeam::with(['project', 'user']);

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
                ->whereHas('user')
                ->join('users', 'teams.user_id', '=', 'users.id')
                ->orderBy('users.name', 'asc')
                ->select('teams.*')
                ->paginate($request->query('limit', 10));

            if ($teams->isEmpty()) {
                return Response::handler(
                    200,
                    'Berhasil mengambil data tim'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data tim',
                ProjectTeamResource::collection($teams),
                Response::pagination($teams)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data tim',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function getById($id): JsonResponse
    {
        try {
            $team = ProjectTeam::with(['project', 'user'])->find($id);

            if (!$team) {
                return Response::handler(
                    400,
                    'Gagal mengambil data tim',
                    [],
                    [],
                    'Data tim tidak ditemukan.'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data tim',
                [ProjectTeamResource::make($team)]
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data tim',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function update(ProjectTeamUpdateRequest $request, $id): JsonResponse
    {
        try {
            $team = ProjectTeam::find($id);

            if (!$team) {
                return Response::handler(
                    400,
                    'Gagal mengubah data tim',
                    [],
                    [],
                    'Data tim tidak ditemukan.'
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
                'Berhasil mengubah data tim',
                [ProjectTeamResource::make($team)]
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengubah data tim',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function softDelete($projectId): JsonResponse
    {
        try {
            $teams = ProjectTeam::withoutTrashed()->where('project_id', $projectId)->get();

            if ($teams->isEmpty()) {
                return Response::handler(
                    400,
                    'Gagal menghapus data tim',
                    [],
                    [],
                    'Tidak ada tim yang ditemukan untuk proyek ini.'
                );
            }

            ProjectTeam::where('project_id', $projectId)->delete();

            return Response::handler(
                200,
                'Berhasil menghapus data tim'
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal menghapus data tim',
                [],
                [],
                $err->getMessage()
            );
        }
    }
}
