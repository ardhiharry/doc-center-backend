<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Http\Requests\ActivityDocRequest;
use App\Http\Resources\ActivityDocResource;
use App\Models\ActivityDoc;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ActivityDocController extends Controller
{
    public function create(ActivityDocRequest $request): JsonResponse
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

            $activityDocByActivity = ActivityDoc::where('activity_id', $request->activity_id)->exists();

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
                    $date = Carbon::now()->format('Ymd');
                    $uuid = Str::uuid()->toString();
                    $randomStr = substr(str_replace('-', '', $uuid), 0, 27);
                    $fileName = "{$date}-{$randomStr}.{$file->extension()}";

                    $filePaths[] = $file->storeAs('activity_docs', $fileName, 'public');
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
                200,
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
            $activityDoc = ActivityDoc::with(['activityDocCategory', 'activity.project.company'])->find($id);

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
