<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Http\Requests\ActivityTeamCreateRequest;
use App\Http\Requests\ActivityTeamUpdateRequest;
use App\Http\Resources\ActivityTeamResource;
use App\Models\ActivityTeam;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityTeamController extends Controller
{
    public function create(ActivityTeamCreateRequest $request):JsonResponse
    {
        try {
            $activityTeam = ActivityTeam::create($request->all());

            return Response::handler(
                201,
                'Berhasil membuat aktivitas tim',
                ActivityTeamResource::make($activityTeam)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal membuat aktivitas tim',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function getAll(Request $request): JsonResponse
    {
        try {
            $activityTeams = ActivityTeam::with(['activity', 'user'])
                ->whereHas('user')
                ->join('tm_users', 'tr_activity_teams.user_id', '=', 'tm_users.id')
                ->orderBy('tm_users.name', 'asc')
                ->paginate($request->query('limit', 10));

            if ($activityTeams->isEmpty()) {
                return Response::handler(
                    200,
                    'Berhasil mengambil data aktivitas tim'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data aktivitas tim',
                ActivityTeamResource::collection($activityTeams),
                Response::pagination($activityTeams)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data aktivitas tim',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $query = ActivityTeam::with(['activity', 'user']);

            $relationList = [
                'activity_name' => ['relation' => 'activity', 'column' => 'name'],
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

                if ($key === 'activity_id') {
                    $activityIds = is_array($value) ? $value : explode(',', $value);
                    $activityIds = array_map('trim', $activityIds);

                    $query->whereHas('activity', function ($q) use ($activityIds) {
                        $q->whereIn('id', $activityIds);
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

            $activityTeams = $query->whereHas('user')
                ->join('tm_users', 'tr_activity_teams.user_id', '=', 'tm_users.id')
                ->orderBy('tm_users.name', 'asc')
                ->paginate($request->query('limit', 10));

            if ($activityTeams->isEmpty()) {
                return Response::handler(
                    200,
                    'Berhasil mengambil data aktivitas tim'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data aktivitas tim',
                ActivityTeamResource::collection($activityTeams),
                Response::pagination($activityTeams)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data aktivitas tim',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function getById($id): JsonResponse
    {
        try {
            $activityTeam = ActivityTeam::with(['activity', 'user'])->find($id);

            if (!$activityTeam) {
                return Response::handler(
                    400,
                    'Gagal mengambil data aktivitas tim',
                    [],
                    [],
                    'Data aktivitas tim tidak ditemukan.'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data aktivitas tim',
                [ActivityTeamResource::make($activityTeam)]
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data aktivitas tim',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function update(ActivityTeamUpdateRequest $request, $id): JsonResponse
    {
        try {
            $activityTeam = ActivityTeam::find($id);

            if (!$activityTeam) {
                return Response::handler(
                    400,
                    'Gagal mengubah data aktivitas tim',
                    [],
                    [],
                    'Data aktivitas tim tidak ditemukan.'
                );
            }

            $activityTeam->update($request->only([
                'activity_id',
                'user_id',
            ]));

            return Response::handler(
                200,
                'Berhasil mengubah data aktivitas tim',
                [ActivityTeamResource::make($activityTeam)]
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengubah data aktivitas tim',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function delete($activityId): JsonResponse
    {
        try {
            $activityTeam = ActivityTeam::where('activity_id', $activityId)->get();

            if ($activityTeam->isEmpty()) {
                return Response::handler(
                    400,
                    'Gagal menghapus data aktivitas tim',
                    [],
                    [],
                    'Data aktivitas tim tidak ditemukan.'
                );
            }

            ActivityTeam::where('activity_id', $activityId)->delete();

            return Response::handler(
                200,
                'Berhasil menghapus data aktivitas tim'
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal menghapus data aktivitas tim',
                [],
                [],
                $err->getMessage()
            );
        }
    }
}
