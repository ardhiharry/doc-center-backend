<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Http\Requests\ActivityDocCategoryCreateRequest;
use App\Http\Requests\ActivityDocCategoryUpdateRequest;
use App\Http\Resources\ActivityDocCategoryResource;
use App\Models\ActivityDocCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityDocCategoryController extends Controller
{
    public function create(ActivityDocCategoryCreateRequest $request): JsonResponse
    {
        try {
            $activityDocCategory = ActivityDocCategory::where('name', $request->name)->exists();

            if ($activityDocCategory) {
                return Response::handler(
                    400,
                    'Gagal membuat kategori dokumen aktivitas',
                    [],
                    [],
                    ['name' => ['Nama kategori dokumen aktivitas sudah ada.']]
                );
            }

            $activityDocCategory = ActivityDocCategory::create($request->all());

            return Response::handler(
                201,
                'Berhasil membuat kategori dokumen aktivitas',
                ActivityDocCategoryResource::make($activityDocCategory)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal membuat kategori dokumen aktivitas',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function getAll(Request $request): JsonResponse
    {
        try {
            $activityDocCategories = ActivityDocCategory::withoutTrashed()
                ->orderBy('name', 'asc')
                ->paginate($request->query('limit', 10));

            if ($activityDocCategories->isEmpty()) {
                return Response::handler(
                    200,
                    'Berhasil mengambil data kategori dokumen aktivitas'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data kategori dokumen aktivitas',
                ActivityDocCategoryResource::collection($activityDocCategories),
                Response::pagination($activityDocCategories)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data kategori dokumen aktivitas',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $query = ActivityDocCategory::withoutTrashed();

            foreach ($request->all() as $key => $value) {
                if (in_array($key, ['name'])) {
                    $query->where($key, 'LIKE', "%{$value}%");
                }
            }

            $activityDocCategories = $query->orderBy('name', 'asc')
                ->paginate($request->query('limit', 10));

            if ($activityDocCategories->isEmpty()) {
                return Response::handler(
                    200,
                    'Berhasil mengambil data kategori dokumen aktivitas'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data kategori dokumen aktivitas',
                ActivityDocCategoryResource::collection($activityDocCategories),
                Response::pagination($activityDocCategories)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data kategori dokumen aktivitas',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function getById($id): JsonResponse
    {
        try {
            $activityDocCategory = ActivityDocCategory::find($id);

            if (!$activityDocCategory) {
                return Response::handler(
                    400,
                    'Gagal mengambil data kategori dokumen aktivitas',
                    [],
                    [],
                    'Data kategori dokumen aktivitas tidak ditemukan.'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data kategori dokumen aktivitas',
                [ActivityDocCategoryResource::make($activityDocCategory)]
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data kategori dokumen aktivitas',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function update(ActivityDocCategoryUpdateRequest $request, $id): JsonResponse
    {
        try {
            $activityDocCategory = ActivityDocCategory::find($id);

            if (!$activityDocCategory) {
                return Response::handler(
                    400,
                    'Gagal mengubah data kategori dokumen aktivitas',
                    [],
                    [],
                    'Data kategori dokumen aktivitas tidak ditemukan.'
                );
            }

            if ($request->name !== $activityDocCategory->name) {
                if (ActivityDocCategory::where('name', $request->name)
                    ->where('id', '!=', $id)
                    ->exists()
                ) {
                    return Response::handler(
                        400,
                        'Gagal mengubah data kategori dokumen aktivitas',
                        [],
                        [],
                        ['name' => ['Nama kategori dokumen aktivitas sudah ada.']]
                    );
                }
            }

            $activityDocCategory->update($request->only([
                'name',
            ]));

            return Response::handler(
                200,
                'Berhasil mengubah data kategori dokumen aktivitas',
                ActivityDocCategoryResource::make($activityDocCategory)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengubah data kategori dokumen aktivitas',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function softDelete($id)
    {
        try {
            $activityDocCategory = ActivityDocCategory::find($id);

            if (!$activityDocCategory) {
                return Response::handler(
                    400,
                    'Gagal menghapus kategori dokumen aktivitas',
                    [],
                    [],
                    'Data kategori dokumen aktivitas tidak ditemukan.'
                );
            }

            $activityDocCategory->delete();

            return Response::handler(
                200,
                'Berhasil menghapus kategori dokumen aktivitas'
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal menghapus kategori dokumen aktivitas',
                [],
                [],
                $err->getMessage()
            );
        }
    }
}
