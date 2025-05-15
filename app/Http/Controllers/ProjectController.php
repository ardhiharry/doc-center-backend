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
                    'Gagal membuat proyek',
                    [],
                    [],
                    ['name' => ['Nama proyek sudah ada.']]
                );
            }

            $project = Project::create($request->all())->refresh();

            return Response::handler(
                201,
                'Berhasil membuat proyek',
                ProjectResource::make($project)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal membuat proyek',
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
                    'Berhasil mengambil data proyek'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data proyek',
                ProjectResource::collection($projects),
                Response::pagination($projects)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data proyek',
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

            $filters = $request->only([
                'name', 'id', 'status', 'company_id', 'project_leader_id'
            ]);

            foreach ($filters as $key => $value) {
                switch ($key) {
                    case 'name':
                        $query->where('name', 'LIKE', "%{$value}%");
                        break;

                    case 'id':
                        $ids = is_array($value) ? $value : explode(',', $value);
                        $query->whereIn('id', array_map('trim', $ids));
                        break;

                    case 'status':
                        $query->where('status', $value);
                        break;

                    case 'company_id':
                        $companyIds = is_array($value) ? $value : explode(',', $value);
                        $query->whereHas('company', function ($q) use ($companyIds) {
                            $q->whereIn('id', array_map('trim', $companyIds));
                        });
                        break;

                    case 'project_leader_id':
                        $leaderIds = is_array($value) ? $value : explode(',', $value);
                        $query->whereIn('project_leader_id', array_map('trim', $leaderIds));
                        break;
                }
            }

            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            if ($startDate && $endDate) {
                $query->whereDate('start_date', '>=', $startDate)
                    ->whereDate('end_date', '<=', $endDate);
            } elseif ($startDate) {
                $query->whereDate('start_date', '>=', $startDate);
            } elseif ($endDate) {
                $query->whereDate('end_date', '<=', $endDate);
            }

            if ($request->filled('year')) {
                $query->whereYear('start_date', $request->query('year'));
            }

            if ($startDate || $endDate) {
                $query->orderBy('start_date', 'desc');
            } else {
                $query->orderBy('name', 'asc');
            }

            $projects = $query->withoutTrashed()
                ->paginate($request->query('limit', 10));

            if ($projects->isEmpty()) {
                return Response::handler(200, 'Berhasil mengambil data proyek');
            }

            return Response::handler(
                200,
                'Berhasil mengambil data proyek',
                ProjectResource::collection($projects),
                Response::pagination($projects)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data proyek',
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
                    'Gagal mengambil data proyek',
                    [],
                    [],
                    'Data proyek tidak ditemukan.'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data proyek',
                [ProjectResource::make($project)]
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data proyek',
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
                    'Gagal mengubah data proyek',
                    [],
                    [],
                    'Data proyek tidak ditemukan.'
                );
            }

            if ($request->name !== $project->name) {
                if (Project::where('name', $request->name)
                    ->where('id', '!=', $project->id)
                    ->exists()
                ) {
                    return Response::handler(
                        400,
                        'Gagal mengubah data proyek',
                        [],
                        [],
                        ['name' => ['Nama proyek sudah ada.']]
                    );
                }
            }

            $project->update($request->only([
                'name',
                'code',
                'client',
                'ppk',
                'support_teams',
                'value',
                'status',
                'progress',
                'company_id',
                'project_leader_id',
                'start_date',
                'end_date',
                'maintenance_date',
            ]));

            return Response::handler(
                200,
                'Berhasil mengubah data proyek',
                ProjectResource::make($project)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengubah data proyek',
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
                    'Gagal menghapus data proyek',
                    [],
                    [],
                    'Data proyek tidak ditemukan.'
                );
            }

            $project->delete();

            return Response::handler(
                200,
                'Berhasil menghapus data proyek'
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal menghapus data proyek',
                [],
                [],
                $err->getMessage()
            );
        }
    }
}
