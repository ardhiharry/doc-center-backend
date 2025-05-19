<?php

namespace App\Http\Requests;

use App\Helpers\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Mews\Purifier\Facades\Purifier;

class ActivityDocUpdateRequest extends FormRequest
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
            'title' => 'sometimes|required|string|max:100',
            'files' => 'sometimes|array',
            'files.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png|max:2048',
            'replace_files' => 'sometimes|array',
            'replace_files.*' => 'required|string',
            'remove_files' => 'sometimes|array',
            'remove_files.*' => 'required|string',
            'description' => 'nullable|string',
            'tags' => 'sometimes|required|array',
            'tags.*' => 'string',
            'activity_id' => [
                'sometimes', 'required',
                Rule::exists('tp_4_activities', 'id')->whereNull('deleted_at'),
            ]
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Judul wajib diisi.',
            'title.string' => 'Judul harus berupa teks.',
            'title.max' => 'Judul tidak boleh lebih dari 100 karakter.',

            'files.array' => 'Files harus berupa array.',
            'files.*.file' => 'Setiap item harus berupa file.',
            'files.*.mimes' => 'Setiap file harus berupa dokumen (pdf, doc, docx, xls, xlsx, ppt, pptx) atau gambar (jpg, jpeg, png).',
            'files.*.max' => 'Ukuran file maksimal 2MB.',

            'replace_files.array' => 'Files harus berupa array.',
            'replace_files.*.required' => 'Setiap item harus diisi.',
            'replace_files.*.string' => 'Setiap item harus berupa string.',

            'remove_files.array' => 'Files harus berupa array.',
            'remove_files.*.required' => 'Setiap item harus diisi.',
            'remove_files.*.string' => 'Setiap item harus berupa string.',

            'description.string' => 'Deskripsi harus berupa teks.',

            'tags.required' => 'Tag wajib diisi.',
            'tags.array' => 'Tag harus berupa array.',
            'tags.*.string' => 'Tag harus berupa teks.',

            'activity_id.required' => 'Proyek wajib dipilih.',
            'activity_id.exists' => 'Proyek tidak ditemukan.',
        ];
    }

    protected function prepareForValidation()
    {
        $data = [];

        if ($this->has('title')) {
            $data['title'] = strip_tags($this->title);
        }

        if ($this->has('description')) {
            $data['description'] = Purifier::clean($this->description);
        }

        if ($this->has('tags')) {
            $data['tags'] = is_string($this->tags) ? json_decode($this->tags, true) : $this->tags;
        }

        if ($this->has('activity_id')) {
            $data['activity_id'] = strip_tags($this->activity_id);
        }

        $this->merge($data);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, Response::handler(
            400,
            'Gagal mengubah dokumen aktivitas',
            [],
            [],
            $validator->errors()
        ));
    }
}
