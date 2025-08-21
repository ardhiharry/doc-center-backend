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
            $filePaths = [];

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $fileData = File::generate($file, 'example_documents');

                    $filePaths[] = $file->storeAs($fileData['path'], $fileData['fileName'], 'public');
                }
            }

            $exampleDoc = ExampleDoc::create([
                'title' => $request->title,
                'files' => $filePaths,
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

            $currentFiles = $exampleDoc->files;

            /**
             * REMOVE FILES
             * query params: remove_files[]
             */
            $removeFiles = $request->remove_files ?? [];

            foreach ($removeFiles as $removePath) {
                $key = array_search($removePath, $currentFiles);
                if ($key !== false) {
                    Storage::disk('public')->delete($removePath);
                    unset($currentFiles[$key]);
                }
            }

            /**
             * REPLACE FILES
             * query params: replace_files[index], files[index]
             */
            $replaceFiles = $request->replace_files ?? [];
            $files = $request->file('files') ?? [];

            foreach ($replaceFiles as $index => $replacePath) {
                $key = array_search($replacePath, $currentFiles);
                if ($key !== false) {
                    Storage::disk('public')->delete($replacePath);

                    $fileData = File::generate($files[$index], 'example_documents');
                    $currentFiles[$key] = $files[$index]->storeAs($fileData['path'], $fileData['fileName'], 'public');

                    unset($files[$index]);
                }
            }

            /**
             * INSERT FILES
             * query params: files[]
             */
            foreach ($files as $file) {
                $fileData = File::generate($file, 'example_documents');
                $currentFiles[] = $file->storeAs($fileData['path'], $fileData['fileName'], 'public');
            }

            $data['files'] = $currentFiles;

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
