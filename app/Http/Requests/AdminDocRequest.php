<?php

namespace App\Http\Requests;

use App\Helpers\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminDocRequest extends FormRequest
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
            'file' => 'sometimes|string',
            'project_id' => [
                'required',
                Rule::exists('tp_1_projects', 'id')->whereNull('deleted_at'),
            ],
            'admin_doc_category_id' => [
                'required',
                Rule::exists('tm_admin_doc_categories', 'id')->whereNull('deleted_at'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul wajib diisi.',
            'title.string' => 'Judul harus berupa teks.',
            'title.max' => 'Judul tidak boleh lebih dari 100 karakter.',

            'file.string' => 'File harus berupa teks.',

            'project_id.required' => 'Project wajib dipilih.',
            'project_id.exists' => 'Project tidak ditemukan.',

            'admin_doc_category_id.required' => 'Kategori wajib dipilih.',
            'admin_doc_category_id.exists' => 'Kategori tidak ditemukan.',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'title' => strip_tags($this->title),
            'project_id' => strip_tags($this->project_id),
            'admin_doc_category_id' => strip_tags($this->admin_doc_category_id),
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, Response::handler(
            400,
            'Gagal membuat dokumen administrasi',
            [],
            [],
            $validator->errors()
        ));
    }
}
