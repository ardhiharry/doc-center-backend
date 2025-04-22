<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Http\Requests\AdminDocCategoryCreateRequest;
use App\Http\Requests\AdminDocCategoryUpdateRequest;
use App\Http\Resources\AdminDocCategoryResource;
use App\Models\AdminDocCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminDocCategoryController extends Controller
{
    public function create(AdminDocCategoryCreateRequest $request): JsonResponse
    {
        try {
            $adminDocCategory = AdminDocCategory::where('name', $request->name)->exists();

            if ($adminDocCategory) {
                return Response::handler(
                    400,
                    'Gagal membuat kategori dokumen administrasi',
                    [],
                    [],
                    ['name' => ['Nama kategori dokumen administrasi sudah ada.']]
                );
            }

            $adminDocCategory = AdminDocCategory::create($request->all());

            return Response::handler(
                201,
                'Berhasil membuat kategori dokumen administrasi',
                AdminDocCategoryResource::make($adminDocCategory)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal membuat kategori dokumen administrasi',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function getAll(Request $request): JsonResponse
    {
        try {
            $adminDocCategories = AdminDocCategory::withoutTrashed()
                ->orderBy('name', 'asc')
                ->paginate(request()->query('limit', 10));

            if ($adminDocCategories->isEmpty()) {
                return Response::handler(
                    200,
                    'Berhasil mengambil data kategori dokumen administrasi'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data kategori dokumen administrasi',
                AdminDocCategoryResource::collection($adminDocCategories),
                Response::pagination($adminDocCategories)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data kategori dokumen administrasi',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $query = AdminDocCategory::withoutTrashed();

            foreach ($request->all() as $key => $value) {
                if (in_array($key, ['name'])) {
                    $query->where($key, 'LIKE', "%{$value}%");
                }
            }

            $adminDocCategories = $query->orderBy('name', 'asc')
                ->paginate($request->query('limit', 10));

            if ($adminDocCategories->isEmpty()) {
                return Response::handler(
                    200,
                    'Berhasil mengambil data kategori dokumen administrasi'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data kategori dokumen administrasi',
                AdminDocCategoryResource::collection($adminDocCategories),
                Response::pagination($adminDocCategories)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data kategori dokumen administrasi',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function getById($id): JsonResponse
    {
        try {
            $adminDocCategory = AdminDocCategory::find($id);

            if (!$adminDocCategory) {
                return Response::handler(
                    400,
                    'Gagal mengambil data kategori dokumen administrasi',
                    [],
                    [],
                    'Data kategori dokumen administrasi tidak ditemukan.'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data kategori dokumen administrasi',
                [AdminDocCategoryResource::make($adminDocCategory)]
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data kategori dokumen administrasi',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function update(AdminDocCategoryUpdateRequest $request, $id): JsonResponse
    {
        try {
            $adminDocCategory = AdminDocCategory::find($id);

            if (!$adminDocCategory) {
                return Response::handler(
                    400,
                    'Gagal mengubah data kategori dokumen administrasi',
                    [],
                    [],
                    'Data kategori dokumen administrasi tidak ditemukan.'
                );
            }

            if ($request->name !== $adminDocCategory->name) {
                if (AdminDocCategory::where('name', $request->name)
                    ->where('id', '!=', $adminDocCategory->id)
                    ->exists()
                ) {
                    return Response::handler(
                        400,
                        'Gagal mengubah data kategori dokumen administrasi',
                        [],
                        [],
                        ['name' => ['Nama kategori dokumen administrasi sudah ada.']]
                    );
                }
            }

            $adminDocCategory->update($request->only([
                'name',
            ]));

            return Response::handler(
                200,
                'Berhasil mengubah data kategori dokumen administrasi',
                AdminDocCategoryResource::make($adminDocCategory)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengubah data kategori dokumen administrasi',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function softDelete($id): JsonResponse
    {
        try {
            $adminDocCategory = AdminDocCategory::find($id);

            if (!$adminDocCategory) {
                return Response::handler(
                    400,
                    'Gagal menghapus kategori dokumen administrasi',
                    [],
                    [],
                    'Data kategori dokumen administrasi tidak ditemukan.'
                );
            }

            $adminDocCategory->delete();

            return Response::handler(
                200,
                'Berhasil menghapus kategori dokumen administrasi'
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal menghapus kategori dokumen administrasi',
                [],
                [],
                $err->getMessage()
            );
        }
    }
}
