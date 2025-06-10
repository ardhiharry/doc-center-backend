<?php

namespace App\Http\Controllers;

use App\Helpers\File;
use App\Helpers\Response;
use App\Http\Requests\AdminDocRequest;
use App\Http\Resources\AdminDocResource;
use App\Models\AdminDoc;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminDocController extends Controller
{
    public function create(AdminDocRequest $request): JsonResponse
    {
        try {
            $adminDoc = AdminDoc::where('title', $request->title)->exists();

            if ($adminDoc) {
                return Response::handler(
                    400,
                    'Gagal membuat dokumen administrasi',
                    [],
                    [],
                    ['title' => ['Judul dokumen administrasi sudah ada.']]
                );
            }

            $adminDoc = AdminDoc::create([
                'title' => $request->title,
                'file' => $request->file,
                'project_id' => $request->project_id,
                'admin_doc_category_id' => $request->admin_doc_category_id
            ]);

            $adminDoc->load('project.company', 'adminDocCategory')->refresh();

            return Response::handler(
                201,
                'Berhasil membuat dokumen administrasi',
                AdminDocResource::make($adminDoc)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal membuat dokumen administrasi',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function getAll(Request $request): JsonResponse
    {
        try {
            $adminDocs = AdminDoc::with(['project.company', 'adminDocCategory'])
                ->withoutTrashed()
                ->orderBy('title', 'asc')
                ->paginate($request->query('limit', 10));

            if ($adminDocs->isEmpty()) {
                return Response::handler(
                    200,
                    'Berhasil mengambil data dokumen administrasi'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data dokumen administrasi',
                AdminDocResource::collection($adminDocs),
                Response::pagination($adminDocs)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data dokumen administrasi',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $query = AdminDoc::with(['project.company', 'adminDocCategory']);

            foreach ($request->all() as $key => $value) {
                if (in_array($key, ['title', 'project_id', 'admin_doc_category_id'])) {
                    $query->where($key, 'LIKE', "%{$value}%");
                }

                if (in_array($key, ['project_id'])) {
                    $query->where($key, $value);
                }
            }

            $adminDocs = $query->withoutTrashed()
                ->orderBy('title', 'asc')
                ->paginate($request->query('limit', 10));

            if ($adminDocs->isEmpty()) {
                return Response::handler(
                    200,
                    'Berhasil mengambil data dokumen administrasi'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data dokumen administrasi',
                AdminDocResource::collection($adminDocs),
                Response::pagination($adminDocs)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data dokumen administrasi',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function getById($id): JsonResponse
    {
        try {
            $adminDoc = AdminDoc::with(['project.company', 'adminDocCategory'])->find($id);

            if (!$adminDoc) {
                return Response::handler(
                    400,
                    'Gagal mengambil data dokumen administrasi',
                    [],
                    [],
                    'Data dokumen administrasi tidak ditemukan.'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data dokumen administrasi',
                [AdminDocResource::make($adminDoc)]
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data dokumen administrasi',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function softDelete($id): JsonResponse
    {
        try {
            $adminDoc = AdminDoc::find($id);

            if (!$adminDoc) {
                return Response::handler(
                    400,
                    'Gagal menghapus data dokumen administrasi',
                    [],
                    [],
                    'Data dokumen administrasi tidak ditemukan.'
                );
            }

            $adminDoc->delete();

            return Response::handler(
                200,
                'Berhasil menghapus data dokumen administrasi'
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal menghapus data dokumen administrasi',
                [],
                [],
                $err->getMessage()
            );
        }
    }
}
