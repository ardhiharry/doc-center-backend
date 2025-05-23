<?php

namespace App\Http\Controllers;

use App\Helpers\File;
use App\Helpers\Response;
use App\Http\Requests\ActivityCategoryCreateRequest;
use App\Http\Requests\ActivityCategoryUpdateRequest;
use App\Http\Resources\ActivityCategoryResource;
use App\Models\ActivityCategory;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use function PHPUnit\Framework\isNull;

class ActivityCategoryController extends Controller
{
    public function create(ActivityCategoryCreateRequest $request): JsonResponse
    {
        try {
            $projectId = $request->project_id;

            $activityCategory = ActivityCategory::where('name', $request->name)
                ->where(function ($query) use ($projectId) {
                    if (!isNull($projectId)) {
                        $query->where('project_id', $projectId);
                    } else {
                        $query->whereNull('project_id');
                    }
                })
                ->exists();

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
                if ($key === 'name') {
                    $query->where($key, 'LIKE', "%{$value}%");
                }

                if ($key === 'project_id') {
                    $projectIds = is_array($value) ? $value : explode(',', $value);
                    $projectIds = array_map('trim', $projectIds);

                    $query->where(function ($q) use ($projectIds) {
                        $hasZero = in_array('0', $projectIds, true) || in_array(0, $projectIds, true) || in_array(null, $projectIds, true);

                        $filteredProjectIds = array_filter($projectIds, fn($id) => $id !== '0' && $id !== 0);

                        $q->where(function ($sub) use ($filteredProjectIds, $hasZero) {
                            if (!empty($filteredProjectIds)) {
                                $sub->whereIn('project_id', $filteredProjectIds);
                            }

                            if ($hasZero) {
                                $sub->orWhereNull('project_id');
                            }
                        });
                    });
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

            $data = $request->only([
                'name',
                'value',
                'note',
                'project_id',
            ]);

            $currentImages = $activityCategory->images ?? [];

            /**
             * REMOVE IMAGES
             * query param: remove_images[]
             */
            $removeImages = $request->input('remove_images') ?? [];

            foreach ($removeImages as $removePath) {
                $key = array_search($removePath, $currentImages);
                if ($key !== false) {
                    Storage::disk('public')->delete($removePath);
                    unset($currentImages[$key]);
                }
            }

            /**
             * REPLACE IMAGES
             * query param: replace_images[index], images[index]
             */
            $replaceTargets = $request->input('replace_images') ?? [];
            $incomingImages = $request->file('images') ?? [];

            foreach ($replaceTargets as $index => $targetPath) {
                $existingIndex = array_search($targetPath, $currentImages);

                if ($existingIndex !== false && isset($incomingImages[$index])) {
                    Storage::disk('public')->delete($targetPath);

                    $newFile = $incomingImages[$index];
                    $fileData = File::generate($newFile, 'activity_categories');
                    $newPath = $newFile->storeAs($fileData['path'], $fileData['fileName'], 'public');

                    $currentImages[$existingIndex] = $newPath;

                    unset($incomingImages[$index]);
                }
            }

            /**
             * ADD NEW IMAGES
             * query param: images[]
             */
            foreach ($incomingImages as $image) {
                $fileData = File::generate($image, 'activity_categories');
                $path = $image->storeAs($fileData['path'], $fileData['fileName'], 'public');

                $currentImages[] = $path;
            }

            $originalImages = $activityCategory->images;
            $updatedImages = array_values($currentImages);

            if ($originalImages !== $updatedImages) {
                if (empty($updatedImages) && $originalImages === null) {
                    $data['images'] = null;
                } else {
                    $data['images'] = $updatedImages;
                }
            }

            $activityCategory->update($data);

            if ($request->has('value')) {
                $projectId = $activityCategory->project_id;
                $project = Project::with('activityCategories')->find($projectId);

                if ($project) {
                    $totalValue = $project->activityCategories()->sum('value');
                    $countValue = $project->activityCategories()->count();
                    $progress = $countValue > 0 ? $totalValue / $countValue : 0;

                    $project->update(['progress' => $progress]);
                }
            }

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
