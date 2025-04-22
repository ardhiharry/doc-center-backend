<?php

namespace App\Http\Requests;

use App\Helpers\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Mews\Purifier\Facades\Purifier;

class ActivityDocRequest extends FormRequest
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
            'file' => 'sometimes|file|mimes:pdf|max:2048',
            'description' => 'nullable|string',
            'tags' => 'required|array',
            'tags.*' => 'string',
            'activity_doc_category_id' => [
                'required',
                Rule::exists('activity_doc_categories', 'id')->whereNull('deleted_at'),
            ],
            'activity_id' => [
                'required',
                Rule::exists('activities', 'id')->whereNull('deleted_at'),
            ],
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Judul wajib diisi.',
            'title.string' => 'Judul harus berupa teks.',
            'title.max' => 'Judul tidak boleh lebih dari 100 karakter.',

            'file.file' => 'File harus berupa file.',
            'file.mimes' => 'File harus berupa PDF.',
            'file.max' => 'Ukuran file maksimal 2MB.',

            'description.string' => 'Deskripsi harus berupa teks.',

            'tags.required' => 'Tag wajib diisi.',
            'tags.array' => 'Tag harus berupa array.',
            'tags.*.string' => 'Tag harus berupa teks.',

            'activity_doc_category_id.required' => 'Kategori wajib dipilih.',
            'activity_doc_category_id.exists' => 'Kategori tidak ditemukan.',

            'activity_id.required' => 'Proyek wajib dipilih.',
            'activity_id.exists' => 'Proyek tidak ditemukan.',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'title' => strip_tags($this->title),
            'description' => Purifier::clean($this->description),
            'tags' => is_string($this->tags) ? json_decode($this->tags, true) : $this->tags,
            'activity_doc_category_id' => strip_tags($this->activity_doc_category_id),
            'activity_id' => strip_tags($this->activity_id),
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, Response::handler(
            400,
            'Failed to create activity document',
            [],
            [],
            $validator->errors()
        ));
    }
}
