<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Http\Requests\ActivityCategoryCreateRequest;
use App\Http\Requests\ActivityCategoryUpdateRequest;
use App\Http\Resources\ActivityCategoryResource;
use App\Models\ActivityCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityCategoryController extends Controller
{
    public function create(ActivityCategoryCreateRequest $request): JsonResponse
    {
        try {
            $activityCategory = ActivityCategory::where('name', $request->name)->exists();

            if ($activityCategory) {
                return Response::handler(
                    400,
                    'Gagal membuat kategori aktivitas',
                    [],
                    [],
                    ['name' => ['Nama kategori aktivitas sudah ada.']]
                );
            }

            $activityCategory = ActivityCategory::create($request->all());

            return Response::handler(
                201,
                'Berhasil membuat kategori aktivitas',
                ActivityCategoryResource::make($activityCategory)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal membuat kategori aktivitas',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function getAll(Request $request): JsonResponse
    {
        try {
            $activityCategories = ActivityCategory::withoutTrashed()
                ->orderBy('name', 'asc')
                ->paginate($request->query('limit', 10));

            if ($activityCategories->isEmpty()) {
                return Response::handler(
                    200,
                    'Berhasil mengambil data kategori aktivitas'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data kategori aktivitas',
                ActivityCategoryResource::collection($activityCategories),
                Response::pagination($activityCategories)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data kategori aktivitas',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $query = ActivityCategory::withoutTrashed();

            foreach ($request->all() as $key => $value) {
                if (in_array($key, ['name'])) {
                    $query->where($key, 'LIKE', "%{$value}%");
                }
            }

            $activityCategories = $query->orderBy('name', 'asc')
                ->paginate($request->query('limit', 10));

            if ($activityCategories->isEmpty()) {
                return Response::handler(
                    200,
                    'Berhasil mengambil data kategori aktivitas'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data kategori aktivitas',
                ActivityCategoryResource::collection($activityCategories),
                Response::pagination($activityCategories)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data kategori aktivitas',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function getById($id): JsonResponse
    {
        try {
            $activityCategory = ActivityCategory::find($id);

            if (!$activityCategory) {
                return Response::handler(
                    400,
                    'Gagal mengambil data kategori aktivitas',
                    [],
                    [],
                    'Data kategori aktivitas tidak ditemukan.'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data kategori aktivitas',
                [ActivityCategoryResource::make($activityCategory)]
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data kategori aktivitas',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function update(ActivityCategoryUpdateRequest $request, $id): JsonResponse
    {
        try {
            $activityCategory = ActivityCategory::find($id);

            if (!$activityCategory) {
                return Response::handler(
                    400,
                    'Gagal mengubah data kategori aktivitas',
                    [],
                    [],
                    'Data kategori aktivitas tidak ditemukan.'
                );
            }

            if ($request->name !== $activityCategory->name) {
                if (ActivityCategory::where('name', $request->name)
                    ->where('id', '!=', $id)
                    ->exists()
                ) {
                    return Response::handler(
                        400,
                        'Gagal mengubah data kategori aktivitas',
                        [],
                        [],
                        ['name' => ['Nama kategori aktivitas sudah ada.']]
                    );
                }
            }

            $activityCategory->update($request->only([
                'name',
                'project_id',
            ]));

            return Response::handler(
                200,
                'Berhasil mengubah data kategori aktivitas',
                ActivityCategoryResource::make($activityCategory)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengubah data kategori aktivitas',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function softDelete($id)
    {
        try {
            $activityCategory = ActivityCategory::find($id);

            if (!$activityCategory) {
                return Response::handler(
                    400,
                    'Gagal menghapus kategori aktivitas',
                    [],
                    [],
                    'Data kategori aktivitas tidak ditemukan.'
                );
            }

            $activityCategory->delete();

            return Response::handler(
                200,
                'Berhasil menghapus kategori aktivitas'
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal menghapus kategori aktivitas',
                [],
                [],
                $err->getMessage()
            );
        }
    }
}
