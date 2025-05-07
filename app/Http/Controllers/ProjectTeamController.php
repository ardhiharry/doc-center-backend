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
            $projectTeam = ProjectTeam::create($request->all());

            $projectTeam->load('project');
            $projectTeam->load('user');

            return Response::handler(
                201,
                'Berhasil membuat tim',
                ProjectTeamResource::make($projectTeam)
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
            $projectTeams = ProjectTeam::with(['project', 'user'])
                ->whereHas('user')
                ->join('tm_users', 'tr_project_teams.user_id', '=', 'tm_users.id')
                ->orderBy('tm_users.name', 'asc')
                ->select('tr_project_teams.*')
                ->paginate($request->query('limit', 10));

            if ($projectTeams->isEmpty()) {
                return Response::handler(
                    200,
                    'Berhasil mengambil data tim'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data tim',
                ProjectTeamResource::collection($projectTeams),
                Response::pagination($projectTeams)
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

                if ($key === 'user_id') {
                    $userIds = is_array($value) ? $value : explode(',', $value);
                    $userIds = array_map('trim', $userIds);

                    $query->whereHas('user', function ($q) use ($userIds) {
                        $q->whereIn('id', $userIds);
                    });
                }
            }

            $projectTeams = $query->whereHas('user')
                ->join('tm_users', 'tr_project_teams.user_id', '=', 'tm_users.id')
                ->orderBy('tm_users.name', 'asc')
                ->select('tr_project_teams.*')
                ->paginate($request->query('limit', 10));

            if ($projectTeams->isEmpty()) {
                return Response::handler(
                    200,
                    'Berhasil mengambil data tim'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data tim',
                ProjectTeamResource::collection($projectTeams),
                Response::pagination($projectTeams)
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
            $projectTeam = ProjectTeam::with(['project', 'user'])->find($id);

            if (!$projectTeam) {
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
                [ProjectTeamResource::make($projectTeam)]
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
            $projectTeam = ProjectTeam::find($id);

            if (!$projectTeam) {
                return Response::handler(
                    400,
                    'Gagal mengubah data tim',
                    [],
                    [],
                    'Data tim tidak ditemukan.'
                );
            }

            $projectTeam->update($request->only([
                'project_id',
                'user_id',
            ]));

            $projectTeam->load('project');
            $projectTeam->load('user');

            return Response::handler(
                200,
                'Berhasil mengubah data tim',
                [ProjectTeamResource::make($projectTeam)]
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

    public function delete($projectId): JsonResponse
    {
        try {
            $projectTeams = ProjectTeam::where('project_id', $projectId)->get();

            if ($projectTeams->isEmpty()) {
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
