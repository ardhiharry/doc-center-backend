<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CharteredAccountantUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'application_date' => 'nullable|date',
            'classification' => 'nullable|string',
            'total' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'images' => 'sometimes|array',
            'images.*' => 'file|mimes:jpg,jpeg,png|max:2048',
            'applicant_id' => [
                'nullable',
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
            'application_date.date' => 'Format tanggal tidak valid',

            'classification.string' => 'Format kode class tidak valid',

            'total.numeric' => 'Format total harus angka',
            'total.min' => 'Format total tidak valid',

            'description.string' => 'Format deskripsi tidak valid',

            'images.array' => 'Format gambar tidak valid',
            'images.*.file' => 'Format gambar tidak valid',
            'images.*.mimes' => 'Format gambar tidak valid',
            'images.*.max' => 'Format gambar tidak valid',

            'applicant_id.exists' => 'Format id applicant tidak valid',

            'project_id.exists' => 'Format id proyek tidak valid',
        ];
    }
}
