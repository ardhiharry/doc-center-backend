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

            $project = Project::create($request->all());

            $project->load('company');

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

            foreach ($request->all() as $key => $value) {
                if (in_array($key, ['name', 'company_id'])) {
                    $query->where($key, 'LIKE', "%{$value}%");
                }

                if ($key === 'id') {
                    $ids = is_array($value) ? $value : explode(',', $value);
                    $ids = array_map('trim', $ids);

                    $query->whereIn('id', $ids);
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

            $projects = $query->withoutTrashed()
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
                'company_id',
                'start_date',
                'end_date'
            ]));

            $project->load('company');

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
