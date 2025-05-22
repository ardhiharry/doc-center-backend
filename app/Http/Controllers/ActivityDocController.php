<?php

namespace App\Http\Controllers;

use App\Helpers\File;
use App\Helpers\Response;
use App\Http\Requests\ActivityDocCreateRequest;
use App\Http\Requests\ActivityDocUpdateRequest;
use App\Http\Resources\ActivityDocResource;
use App\Models\ActivityDoc;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ActivityDocController extends Controller
{
    public function create(ActivityDocCreateRequest $request): JsonResponse
    {
        try {
            $activityDoc = ActivityDoc::where('title', $request->title)->exists();

            if ($activityDoc) {
                return Response::handler(
                    400,
                    'Gagal membuat dokumen aktivitas',
                    [],
                    [],
                    ['title' => ['Judul dokumen aktivitas sudah ada.']]
                );
            }

            $activityDocByActivity = ActivityDoc::where('activity_id', $request->activity_id)
                ->whereNull('deleted_at')
                ->exists();

            if ($activityDocByActivity) {
                return Response::handler(
                    400,
                    'Gagal membuat dokumen aktivitas',
                    [],
                    [],
                    ['activity_id' => ['Aktivitas sudah ada.']]
                );
            }

            $filePaths = null;

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $fileData = File::generate($file, 'activity_docs');

                    $filePaths[] = $file->storeAs($fileData['path'], $fileData['fileName'], 'public');
                }
            }

            $activityDoc = ActivityDoc::create([
                'title' => $request->title,
                'files' => $filePaths,
                'description' => $request->description,
                'tags' => $request->tags,
                'activity_id' => $request->activity_id
            ])->refresh()->load('activity.project.company');

            return Response::handler(
                201,
                'Berhasil membuat dokumen aktivitas',
                ActivityDocResource::make($activityDoc)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal membuat dokumen aktivitas',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function getAll(Request $request): JsonResponse
    {
        try {
            $activityDocs = ActivityDoc::with('activity.project.company')
                ->withoutTrashed()
                ->orderBy('title', 'asc')
                ->paginate($request->query('limit', 10));

            if ($activityDocs->isEmpty()) {
                return Response::handler(
                    200,
                    'Berhasil mengambil data dokumen aktivitas'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data dokumen aktivitas',
                ActivityDocResource::collection($activityDocs),
                Response::pagination($activityDocs)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data dokumen aktivitas',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $query = ActivityDoc::with('activity.project.company');

            foreach ($request->all() as $key => $value) {
                if (in_array($key, ['id', 'title'])) {
                    $query->where($key, 'LIKE', "%{$value}%");
                }

                if ($key === 'activity_id') {
                    $activityIds = is_array($value) ? $value : explode(',', $value);
                    $activityIds = array_map('trim', $activityIds);

                    $query->whereHas('activity', function ($q) use ($activityIds) {
                        $q->whereIn('id', $activityIds);
                    });
                }

                if ($key === 'description') {
                    $descriptions = is_array($value) ? $value : explode(',', $value);
                    $descriptions = array_map('trim', $descriptions);

                    foreach ($descriptions as $description) {
                        $query->orWhere('description', 'LIKE', "%{$description}%");
                    }
                }

                if ($key === 'tags') {
                    $tags = is_array($value) ? $value : explode(',', $value);
                    $tags = array_map('trim', $tags);

                    foreach ($tags as $tag) {
                        $query->orWhereJsonContains('tags', $tag);
                    }
                }

                if ($key === 'project_id') {
                    $projectIds = is_array($value) ? $value : explode(',', $value);
                    $projectIds = array_map('trim', $projectIds);

                    $query->whereHas('activity.project', function ($q) use ($projectIds) {
                        $q->whereIn('id', $projectIds);
                    });
                }
            }

            $activityDocs = $query->withoutTrashed()
                ->orderBy('title', 'asc')
                ->paginate($request->query('limit', 10));

            if ($activityDocs->isEmpty()) {
                return Response::handler(
                    200,
                    'Berhasil mengambil data dokumen aktivitas'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data dokumen aktivitas',
                ActivityDocResource::collection($activityDocs),
                Response::pagination($activityDocs)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data dokumen aktivitas',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function getAllTags(): JsonResponse
    {
        $activityDocs = ActivityDoc::all();

        if ($activityDocs->isEmpty()) {
            return Response::handler(
                200,
                'Berhasil mengambil data tag dokumen aktivitas'
            );
        }

        return Response::handler(
            200,
            'Berhasil mengambil data tag dokumen aktivitas',
            $activityDocs->pluck('tags')->flatten()->unique()->values()
        );
    }

    public function getById($id): JsonResponse
    {
        try {
            $activityDoc = ActivityDoc::with(['activity.project.company'])->find($id);

            if (!$activityDoc) {
                return Response::handler(
                    400,
                    'Gagal mengambil data dokumen aktivitas',
                    [],
                    [],
                    'Data dokumen aktivitas tidak ditemukan.'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data dokumen aktivitas',
                [ActivityDocResource::make($activityDoc)]
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data dokumen aktivitas',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function update(ActivityDocUpdateRequest $request, $id): JsonResponse
    {
        try {
            $activityDoc = ActivityDoc::find($id);

            if (!$activityDoc) {
                return Response::handler(
                    400,
                    'Gagal mengubah dokumen aktivitas',
                    [],
                    [],
                    'Data dokumen aktivitas tidak ditemukan.'
                );
            }

            $data = $request->only([
                'title',
                'description',
                'tags',
                'activity_id',
            ]);

            $currentFiles = $activityDoc->files ?? [];

            /**
             * REMOVE FILES
             * query params: remove_files[]
             */
            $removeFiles = $request->input('remove_files') ?? [];

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
            $replaceTargets = $request->input('replace_files') ?? [];
            $insertFiles = $request->file('files') ?? [];

            foreach ($replaceTargets as $index => $targetPath) {
                $existingIndex = array_search($targetPath, $currentFiles);

                if ($existingIndex !== false && isset($insertFiles[$index])) {
                    Storage::disk('public')->delete($targetPath);

                    $newFile = $insertFiles[$index];
                    $fileData = File::generate($newFile, 'activity_docs');
                    $newPath = $newFile->storeAs($fileData['path'], $fileData['fileName'], 'public');

                    $currentFiles[$existingIndex] = $newPath;

                    unset($insertFiles[$index]);
                }
            }

            /**
             * INSERT FILES
             * query params: files[]
             */
            foreach ($insertFiles as $file) {
                $fileData = File::generate($file, 'activity_docs');
                $path = $file->storeAs($fileData['path'], $fileData['fileName'], 'public');

                $currentFiles[] = $path;
            }

            $originalFiles = $activityDoc->files;
            $updatedFiles = array_values($currentFiles);

            if ($originalFiles !== $updatedFiles) {
                if (empty($updatedFiles) && $originalFiles === null) {
                    $data['files'] = null;
                } else {
                    $data['files'] = $updatedFiles;
                }
            }

            $activityDoc->update($data);

            return Response::handler(
                200,
                'Berhasil mengubah dokumen aktivitas',
                [ActivityDocResource::make($activityDoc)]
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengubah dokumen aktivitas',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function softDelete($id): JsonResponse
    {
        try {
            $activityDoc = ActivityDoc::find($id);

            if (!$activityDoc) {
                return Response::handler(
                    400,
                    'Gagal menghapus dokumen aktivitas',
                    [],
                    [],
                    'Data dokumen aktivitas tidak ditemukan.'
                );
            }

            $activityDoc->delete();

            return Response::handler(
                200,
                'Berhasil menghapus dokumen aktivitas'
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal menghapus dokumen aktivitas',
                [],
                [],
                $err->getMessage()
            );
        }
    }
}
