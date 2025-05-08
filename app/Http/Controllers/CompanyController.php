<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Http\Requests\CompanyCreateRequest;
use App\Http\Requests\CompanyUpdateRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    public function create(CompanyCreateRequest $request): JsonResponse
    {
        try {
            $company = Company::where('name', $request->name)->exists();

            if ($company) {
                return Response::handler(
                    400,
                    'Gagal membuat perusahaan',
                    [],
                    [],
                    ['name' => ['Nama perusahaan sudah ada.']]
                );
            }

            $filePath = '/companies/default.png';

            if ($request->hasFile('director_signature')) {
                $date = Carbon::now()->format('Ymd');
                $uuid = Str::uuid()->toString();
                $randomStr = substr(str_replace('-', '', $uuid), 0, 27);
                $fileName = "{$date}-{$randomStr}.{$request->file('director_signature')->extension()}";

                $filePath = $request->file('director_signature')->storeAs('companies', $fileName, 'public');
            }

            $company = Company::create([
                'name' => $request->name,
                'address' => $request->address,
                'director_name' => $request->director_name,
                'director_phone' => $request->director_phone,
                'director_signature' => $filePath
            ]);

            return Response::handler(
                201,
                'Perusahaan berhasil dibuat',
                CompanyResource::make($company)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal membuat perusahaan',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function getAll(Request $request): JsonResponse
    {
        try {
            $companies = Company::withoutTrashed()
                ->orderBy('name', 'asc')
                ->paginate($request->query('limit', 10));

            if ($companies->isEmpty()) {
                return Response::handler(
                    200,
                    'Berhasil mengambil data perusahaan'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data perusahaan',
                CompanyResource::collection($companies),
                Response::pagination($companies)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data perusahaan',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $query = Company::query();

            foreach ($request->all() as $key => $value) {
                if (in_array($key, ['name', 'address', 'director_name', 'director_phone'])) {
                    $query->where($key, 'LIKE', "%{$value}%");
                }
            }

            $companies = $query->withoutTrashed()
                ->orderBy('name', 'asc')
                ->paginate($request->query('limit', 10));

            if ($companies->isEmpty()) {
                return Response::handler(
                    200,
                    'Berhasil mengambil data perusahaan'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data perusahaan',
                CompanyResource::collection($companies),
                Response::pagination($companies)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data perusahaan',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function getById($id): JsonResponse
    {
        try {
            $company = Company::withoutTrashed()->find($id);

            if (!$company) {
                return Response::handler(
                    400,
                    'Gagal mengambil data perusahaan',
                    [],
                    [],
                    'Data perusahaan tidak ditemukan.'
                );
            }

            return Response::handler(
                200,
                'Berhasil mengambil data perusahaan',
                [CompanyResource::make($company)]
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengambil data perusahaan',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function update(CompanyUpdateRequest $request, $id): JsonResponse
    {
        try {
            $company = Company::withoutTrashed()->find($id);

            if (!$company) {
                return Response::handler(
                    400,
                    'Gagal mengubah data perusahaan',
                    [],
                    [],
                    'Data perusahaan tidak ditemukan.'
                );
            }

            if ($request->name !== $company->name) {
                if (Company::where('name', $request->name)
                    ->where('id', '!=', $id)
                    ->exists()
                ) {
                    return Response::handler(
                        400,
                        'Gagal mengubah data perusahaan',
                        [],
                        [],
                        ['name' => ['Nama perusahaan sudah ada.']]
                    );
                }
            }

            if ($request->hasFile('director_signature')) {
                $date = Carbon::now()->format('Ymd');
                $uuid = Str::uuid()->toString();
                $randomStr = substr(str_replace('-', '', $uuid), 0, 27);
                $fileName = "{$date}-{$randomStr}.{$request->file('director_signature')->extension()}";

                $filePath = $request->file('director_signature')->storeAs('companies', $fileName, 'public');

                $company->director_signature = $filePath;
            }

            $company->fill($request->only([
                'name',
                'address',
                'director_name',
                'director_phone',
            ]))->save();

            return Response::handler(
                200,
                'Berhasil mengubah data perusahaan',
                CompanyResource::make($company)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal mengubah data perusahaan',
                [],
                [],
                $err->getMessage()
            );
        }
    }

    public function softDelete($id): JsonResponse
    {
        try {
            $company = Company::withoutTrashed()->find($id);

            if (!$company) {
                return Response::handler(
                    400,
                    'Gagal menghapus data perusahaan',
                    [],
                    [],
                    'Data perusahaan tidak ditemukan.'
                );
            }

            $company->delete();

            return Response::handler(
                200,
                'Berhasil menghapus data perusahaan'
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Gagal menghapus data perusahaan',
                [],
                [],
                $err->getMessage()
            );
        }
    }
}
