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
                    'Failed to create company',
                    [],
                    'Company name already exists.'
                );
            }

            $filePath = null;

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
                200,
                'Company created successfully',
                CompanyResource::make($company)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to create company',
                [],
                $err->getMessage()
            );
        }
    }

    public function getAll(): JsonResponse
    {
        try {
            $companies = Company::withoutTrashed()->get();

            if ($companies->isEmpty()) {
                return Response::handler(
                    200,
                    'Companies retrieved successfully'
                );
            }

            return Response::handler(
                200,
                'Companies retrieved successfully',
                CompanyResource::collection($companies)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to retrieve companies',
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

            $companies = $query->withoutTrashed()->get();

            if ($companies->isEmpty()) {
                return Response::handler(
                    200,
                    'Companies retrieved successfully'
                );
            }

            return Response::handler(
                200,
                'Companies retrieved successfully',
                CompanyResource::collection($companies)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to retrieve companies',
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
                    'Failed to retrieve company',
                    [],
                    'Company not found.'
                );
            }

            return Response::handler(
                200,
                'Company retrieved successfully',
                [CompanyResource::make($company)]
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to retrieve company',
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
                    'Failed to update company',
                    [],
                    'Company not found.'
                );
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
                'Company updated successfully',
                CompanyResource::make($company)
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to update company',
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
                    'Failed to delete company',
                    [],
                    'Company not found.'
                );
            }

            $company->delete();

            return Response::handler(
                200,
                'Company deleted successfully'
            );
        } catch (\Exception $err) {
            return Response::handler(
                500,
                'Failed to delete company',
                [],
                $err->getMessage()
            );
        }
    }
}
