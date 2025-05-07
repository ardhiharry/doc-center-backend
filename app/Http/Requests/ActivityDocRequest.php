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
            'files' => 'sometimes|array',
            'files.*' => 'file|mimes:pdf,jpg,jpeg,png|max:2048',
            'description' => 'nullable|string',
            'tags' => 'required|array',
            'tags.*' => 'string',
            'activity_id' => [
                'required',
                Rule::exists('tp_4_activities', 'id')->whereNull('deleted_at'),
            ],
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
            'files.*.mimes' => 'Setiap file harus berupa PDF atau gambar.',
            'files.*.max' => 'Ukuran file maksimal 2MB.',

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
        $this->merge([
            'title' => strip_tags($this->title),
            'description' => Purifier::clean($this->description),
            'tags' => is_string($this->tags) ? json_decode($this->tags, true) : $this->tags,
            'activity_id' => strip_tags($this->activity_id),
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, Response::handler(
            400,
            'Gagal membuat dokumen aktivitas',
            [],
            [],
            $validator->errors()
        ));
    }
}
