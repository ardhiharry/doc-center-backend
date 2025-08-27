<?php

namespace App\Http\Controllers;

use App\Helpers\File;
use App\Helpers\Pagination;
use App\Helpers\Response;
use App\Http\Requests\CreateExampleDocument;
use App\Http\Requests\UpdateExampleDocument;
use App\Http\Resources\ExampleDocumentResource;
use App\Models\ExampleDoc;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as Status;

class ExampleDocController extends Controller
{
    public function store(CreateExampleDocument $request): JsonResponse
    {
        try {
            $exampleDoc = ExampleDoc::create([
                'title' => $request->title,
                'file' => $request->file,
            ]);

            return Response::handler(
                Status::HTTP_CREATED,
                'Berhasil membuat dokumen contoh',
                ExampleDocumentResource::make($exampleDoc)
            );
        } catch (\Exception $err) {
            return Response::handler(
                Status::HTTP_INTERNAL_SERVER_ERROR,
                'Gagal membuat dokumen contoh',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function index(): JsonResponse
    {
        try {
            $limit = request('limit', 10);
            $search = [
                'id' => request('id'),
                'title' => request('title'),
            ];

            $exampleDocs = ExampleDoc::query()
                ->search($search)
                ->orderBy('title', 'asc')
                ->paginate($limit)
                ->withQueryString();

            return Response::handler(
                Status::HTTP_OK,
                'Berhasil mengambil data dokumen contoh',
                ExampleDocumentResource::collection($exampleDocs),
                Pagination::paginate($exampleDocs),
            );
        } catch (\Exception $err) {
            return Response::handler(
                Status::HTTP_INTERNAL_SERVER_ERROR,
                'Gagal mengambil data dokumen contoh',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function show(ExampleDoc $exampleDoc): JsonResponse
    {
        try {
            return Response::handler(
                Status::HTTP_OK,
                'Berhasil mengambil data dokumen contoh',
                ExampleDocumentResource::make($exampleDoc),
            );
        } catch (\Exception $err) {
            return Response::handler(
                Status::HTTP_INTERNAL_SERVER_ERROR,
                'Gagal mengambil data dokumen contoh',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function update(UpdateExampleDocument $request, ExampleDoc $exampleDoc): JsonResponse
    {
        try {
            $data = array_filter([
                'title' => $request->title,
                'files' => $request->files
            ]);

            $exampleDoc->update($data);

            return Response::handler(
                Status::HTTP_OK,
                'Berhasil mengubah dokumen contoh',
                ExampleDocumentResource::make($exampleDoc),
            );
        } catch (\Exception $err) {
            return Response::handler(
                Status::HTTP_INTERNAL_SERVER_ERROR,
                'Gagal mengubah dokumen contoh',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function destroy(ExampleDoc $exampleDoc): JsonResponse
    {
        try {
            if (is_array($exampleDoc->files)) {
                foreach ($exampleDoc->files as $filePath) {
                    if (Storage::disk('public')->exists($filePath)) {
                        Storage::disk('public')->delete($filePath);
                    }
                }
            }

            $exampleDoc->delete();

            return Response::handler(
                Status::HTTP_OK,
                'Berhasil menghapus dokumen contoh',
            );
        } catch (\Exception $err) {
            return Response::handler(
                Status::HTTP_INTERNAL_SERVER_ERROR,
                'Gagal menghapus dokumen contoh',
                [],
                [],
                $err->getMessage()
            );
        }
    }
}
