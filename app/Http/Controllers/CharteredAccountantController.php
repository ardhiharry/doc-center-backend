<?php

namespace App\Http\Controllers;

use App\Helpers\File;
use App\Helpers\Response;
use App\Http\Requests\CharteredAccountantCreateRequest;
use App\Http\Resources\CharteredAccountantResource;
use App\Models\CharteredAccountant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CharteredAccountantController extends Controller
{
    public function create(CharteredAccountantCreateRequest $request): JsonResponse
    {
        try {
            $filePaths = null;

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $fileData = File::generate($image, 'chartered_accountants');

                    $filePaths[] = $image->storeAs($fileData['path'], $fileData['fileName'], 'public');
                }
            }

            $charteredAccountant = CharteredAccountant::create([
                'application_date' => $request->application_date,
                'classification' => $request->classification,
                'total' => $request->total,
                'description' => $request->description,
                'images' => $filePaths,
                'applicant_id' => $request->applicant_id,
                'project_id' => $request->project_id,
            ]);

            return Response::handler(
                201,
                'Berhasil membuat chartered accountant',
                CharteredAccountantResource::make($charteredAccountant)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal membuat chartered accountant',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function getAll(Request $request): JsonResponse
    {
        try {
            $charteredAccoountants = CharteredAccountant::with('applicant', 'project')
                ->withoutTrashed()
                ->orderBy('application_date', 'desc')
                ->paginate($request->query('limit', 10));

            if ($charteredAccoountants->isEmpty()) {
                return Response::handler(
                    200,
                    'Berhasil mengambil data chartered accountant'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data chartered accountant',
                CharteredAccountantResource::collection($charteredAccoountants),
                Response::pagination($charteredAccoountants)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data chartered accountant',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $query = CharteredAccountant::query();

            foreach ($request->all() as $key => $value) {
                if ($key === 'application_date') {
                    $query->whereDate('application_date', $value);
                }

                if (in_array($key, ['classification', 'total', 'description'])) {
                    $query->where($key, 'LIKE', "%{$value}%");
                }

                if ($key === 'applicant_id') {
                    $applicantIds = is_array($value) ? $value : explode(',', $value);
                    $applicantIds = array_map('trim', $applicantIds);

                    $query->whereHas('applicant', function ($q) use ($applicantIds) {
                        $q->whereIn('id', $applicantIds);
                    });
                }

                if ($key === 'project_id') {
                    $projectIds = is_array($value) ? $value : explode(',', $value);
                    $projectIds = array_map('trim', $projectIds);

                    $query->whereHas('project', function ($q) use ($projectIds) {
                        $q->whereIn('id', $projectIds);
                    });
                }
            }

            $charteredAccoountants = $query->orderBy('application_date', 'desc')
                ->paginate($request->query('limit', 10));

            if ($charteredAccoountants->isEmpty()) {
                return Response::handler(
                    200,
                    'Berhasil mengambil data chartered accountant'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data chartered accountant',
                CharteredAccountantResource::collection($charteredAccoountants),
                Response::pagination($charteredAccoountants)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data chartered accountant',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function getById($id): JsonResponse
    {
        try {
            $charteredAccoountant = CharteredAccountant::with(['applicant', 'project'])->find($id);

            if (!$charteredAccoountant) {
                return Response::handler(
                    400,
                    'Gagal mengambil data chartered accountant',
                    [],
                    [],
                    'Data chartered accountant tidak ditemukan.'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data chartered accountant',
                CharteredAccountantResource::make($charteredAccoountant)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data chartered accountant',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $charteredAccoountant = CharteredAccountant::find($id);

            if (!$charteredAccoountant) {
                return Response::handler(
                    400,
                    'Gagal mengambil data chartered accountant',
                    [],
                    [],
                    'Data chartered accountant tidak ditemukan.'
                );
            }

            $data = $request->only([
                'application_date',
                'classification',
                'total',
                'description',
                'applicant_id',
                'project_id',
            ]);

            $currentImages = $charteredAccoountant->images ?? [];

            /**
             * REMOVE FILES
             * query params: remove_files[]
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
             * REPLACE FILES
             * query params: replace_files[index], files[index]
             */
            $replaceTargets = $request->input('replace_images') ?? [];
            $insertImages = $request->file('images') ?? [];

            foreach ($replaceTargets as $index => $targetPath) {
                $existingIndex = array_search($targetPath, $currentImages);

                if ($existingIndex !== false && isset($insertImages[$index])) {
                    Storage::disk('public')->delete($targetPath);

                    $newFile = $insertImages[$index];
                    $fileData = File::generate($newFile, 'chartered_accountants');
                    $newPath = $newFile->storeAs($fileData['path'], $fileData['fileName'], 'public');

                    $currentImages[$existingIndex] = $newPath;

                    unset($insertImages[$index]);
                }
            }

            /**
             * INSERT FILES
             * query params: files[]
             */
            foreach ($insertImages as $file) {
                $fileData = File::generate($file, 'chartered_accountants');
                $path = $file->storeAs($fileData['path'], $fileData['fileName'], 'public');

                $currentImages[] = $path;
            }

            $originalImages = $charteredAccoountant->images;
            $updatedImages = array_values($currentImages);

            if ($originalImages !== $updatedImages) {
                if (empty($updatedImages) && $originalImages === null) {
                    $data['images'] = null;
                } else {
                    $data['images'] = $updatedImages;
                }
            }

            $charteredAccoountant->update($data);

            return Response::handler(
                200,
                'Berhasil mengubah data chartered accountant',
                CharteredAccountantResource::make($charteredAccoountant)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengubah data chartered accountant',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function softDelete($id): JsonResponse
    {
        try {
            $charteredAccoountant = CharteredAccountant::find($id);

            if (!$charteredAccoountant) {
                return Response::handler(
                    400,
                    'Gagal menghapus chartered accountant',
                    [],
                    [],
                    'Data chartered accountant tidak ditemukan.'
                );
            }

            $charteredAccoountant->delete();

            return Response::handler(
                200,
                'Berhasil menghapus chartered accountant'
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal menghapus chartered accountant',
                [],
                [],
                $err->getMessage()
            );
        }
    }
}
