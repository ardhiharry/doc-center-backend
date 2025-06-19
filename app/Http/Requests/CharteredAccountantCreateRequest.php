<?php

namespace App\Http\Requests;

use App\Helpers\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CharteredAccountantCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'application_date' => 'required|date',
            'classification' => 'required|string',
            'total' => 'required|numeric|min:0',
            'description' => 'required|string',
            'images' => 'sometimes|array',
            'images.*' => 'file|mimes:jpg,jpeg,png|max:2048',
            'applicant_id' => [
                'required',
                Rule::exists('tm_users', 'id')->whereNull('deleted_at'),
            ],
            'project_id' => [
                'nullable',
                Rule::exists('tp_1_projects', 'id')->whereNull('deleted_at'),
            ],
        ];
    }

    public function messages()
    {
        return [
            'application_date.required' => 'Format tanggal tidak boleh kosong.',
            'application_date.date' => 'Format tanggal tidak valid.',

            'classification.required' => 'Format kode class tidak boleh kosong.',
            'classification.string' => 'Format kode class tidak valid.',

            'total.required' => 'Format total tidak boleh kosong.',
            'total.numeric' => 'Format total harus angka.',
            'total.min' => 'Format total tidak valid.',

            'description.required' => 'Format deskripsi tidak boleh kosong.',
            'description.string' => 'Format deskripsi tidak valid.',

            'images.array' => 'Format gambar tidak valid.',
            'images.*.file' => 'Format gambar tidak valid.',
            'images.*.mimes' => 'Format gambar tidak valid.',
            'images.*.max' => 'Format gambar tidak valid.',

            'applicant_id.required' => 'Format id applicant tidak boleh kosong.',
            'applicant_id.exists' => 'Format id applicant tidak valid.',

            'project_id.required' => 'Format id project tidak boleh kosong.',
            'project_id.exists' => 'Format id proyek tidak valid.',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'application_date' => strip_tags($this->application_date),
            'classification' => strip_tags($this->classification),
            'total' => strip_tags($this->total),
            'description' => strip_tags($this->description),
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, Response::handler(
            400,
            'Failed to create chartered accountant',
            [],
            [],
            $validator->errors()
        ));
    }
}
