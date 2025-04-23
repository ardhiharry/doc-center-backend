<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Http\Requests\ActivityCreateRequest;
use App\Http\Requests\ActivityUpdateRequest;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function create(ActivityCreateRequest $request): JsonResponse
    {
        try {
            $activity = Activity::where('title', $request->title)->exists();

            if ($activity) {
                return Response::handler(
                    400,
                    'Gagal membuat aktivitas',
                    [],
                    [],
                    ['title' => ['Judul aktivitas sudah ada.']]
                );
            }

            $activity = Activity::create($request->all());

            $activity->load('project.company');

            return Response::handler(
                200,
                'Berhasil membuat aktivitas',
                ActivityResource::make($activity)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal membuat aktivitas',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function getAll(Request $request): JsonResponse
    {
        try {
            $activities = Activity::with('project.company')
                ->withoutTrashed()
                ->orderBy('title', 'asc')
                ->paginate($request->query('limit', 10));

            if ($activities->isEmpty()) {
                return Response::handler(
                    200,
                    'Berhasil mengambil data aktivitas'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data aktivitas',
                ActivityResource::collection($activities),
                Response::pagination($activities)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data aktivitas',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $query = Activity::with('project.company');

            foreach ($request->all() as $key => $value) {
                if ($key === 'title') {
                    $query->where($key, 'LIKE', "%{$value}%");
                }

                if ($key === 'project_id') {
                    $projectIds = is_array($value) ? $value : explode(',', $value);
                    $projectIds = array_map('trim', $projectIds);

                    $query->whereHas('project', function ($q) use ($projectIds) {
                        $q->whereIn('id', $projectIds);
                    });
                }
            }

            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            if ($startDate && $endDate) {
              $query->whereDate('start_date', '>=', $startDate)
                ->whereDate('end_date', '<=', $endDate);
            } else if ($startDate) {
                $query->whereDate('start_date', '=', $startDate);
            } else if ($endDate) {
                $query->whereDate('end_date', '=', $endDate);
            }

            $activities = $query->withoutTrashed()
                ->orderBy('title', 'asc')
                ->paginate($request->query('limit', 10));

            if ($activities->isEmpty()) {
                return Response::handler(
                    200,
                    'Berhasil mengambil data aktivitas'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data aktivitas',
                ActivityResource::collection($activities),
                Response::pagination($activities)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data aktivitas',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function getById($id): JsonResponse
    {
        try {
            $activity = Activity::with('project.company')->find($id);

            if (!$activity) {
                return Response::handler(
                    400,
                    'Gagal mengambil data aktivitas',
                    [],
                    [],
                    'Data aktivitas tidak ditemukan.'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data aktivitas',
                [ActivityResource::make($activity)]
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data aktivitas',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function update(ActivityUpdateRequest $request, $id): JsonResponse
    {
        try {
            $activity = Activity::find($id);

            if (!$activity) {
                return Response::handler(
                    400,
                    'Gagal mengubah data aktivitas',
                    [],
                    [],
                    'Data aktivitas tidak ditemukan.'
                );
            }

            if ($request->title !== $activity->title) {
                if (Activity::where('title', $request->title)
                    ->where('id', '!=', $id)
                    ->exists()
                ) {
                    return Response::handler(
                        400,
                        'Gagal mengubah data aktivitas',
                        [],
                        [],
                        ['title' => ['Judul aktivitas sudah ada.']]
                    );
                }
            }

            $activity->update($request->only([
                'title',
                'start_date',
                'end_date',
                'project_id'
            ]));

            $activity->load('project.company');

            return Response::handler(
                200,
                'Berhasil mengubah data aktivitas',
                ActivityResource::make($activity)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengubah data aktivitas',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function softDelete($id): JsonResponse
    {
        try {
            $activity = Activity::find($id);

            if (!$activity) {
                return Response::handler(
                    400,
                    'Gagal menghapus data aktivitas',
                    [],
                    [],
                    'Data aktivitas tidak ditemukan.'
                );
            }

            $activity->delete();

            return Response::handler(
                200,
                'Berhasil menghapus data aktivitas'
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal menghapus data aktivitas',
                [],
                [],
                $err->getMessage()
            );
        }
    }
}
