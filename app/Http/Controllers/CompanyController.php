<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Http\Requests\CompanyCreateRequest;
use App\Http\Requests\CompanyUpdateRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    public function create(CompanyCreateRequest $request)
    {
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
    }

    public function getAll()
    {
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
            $companies
        );
    }

    public function getById($id)
    {
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
            [$company]
        );
    }

    public function update(CompanyUpdateRequest $request, $id)
    {
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
            $company
        );
    }

    public function softDelete($id)
    {
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
    }
}
