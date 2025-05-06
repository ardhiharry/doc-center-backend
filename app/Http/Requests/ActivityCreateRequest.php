<?php

namespace App\Http\Requests;

use App\Helpers\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ActivityCreateRequest extends FormRequest
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
            'title' => 'required|string|max:100',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'activity_category_id' => [
                'required',
                Rule::exists('tp_3_activity_categories', 'id')->whereNull('deleted_at'),
            ],
            'project_id' => [
                'required',
                Rule::exists('tp_1_projects', 'id')->whereNull('deleted_at'),
            ],
            'author_id' => [
                'required',
                Rule::exists('tm_users', 'id')->whereNull('deleted_at'),
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul wajib diisi.',
            'title.string' => 'Judul harus berupa teks.',
            'title.max' => 'Judul tidak boleh lebih dari 100 karakter.',

            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'start_date.date' => 'Tanggal mulai harus berupa tanggal.',
            'start_date.before_or_equal' => 'Tanggal mulai harus sebelum atau sama dengan tanggal selesai.',

            'end_date.required' => 'Tanggal selesai wajib diisi.',
            'end_date.date' => 'Tanggal selesai harus berupa tanggal.',
            'end_date.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',

            'activity_category_id.required' => 'Kategori aktivitas wajib dipilih.',
            'activity_category_id.exists' => 'Kategori aktivitas tidak ditemukan.',

            'project_id.required' => 'Proyek wajib dipilih.',
            'project_id.exists' => 'Proyek tidak ditemukan.',

            'author_id.required' => 'Author wajib dipilih.',
            'author_id.exists' => 'Author tidak ditemukan.',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'title' => strip_tags($this->title),
            'start_date' => strip_tags($this->start_date),
            'end_date' => strip_tags($this->end_date),
            'activity_category_id' => strip_tags($this->activity_category_id),
            'project_id' => strip_tags($this->project_id),
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, Response::handler(
            400,
            'Gagal membuat aktivitas',
            [],
            [],
            $validator->errors()
        ));
    }
}
