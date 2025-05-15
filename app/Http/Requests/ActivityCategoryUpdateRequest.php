<?php

namespace App\Http\Requests;

use App\Helpers\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ActivityCategoryUpdateRequest extends FormRequest
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
            'name' => 'sometimes|required|string|max:255',
            'value' => 'sometimes|required|numeric',
            'note' => 'sometimes|required|string',
            'images' => 'sometimes|array',
            'images.*' => 'file|mimes:jpg,jpeg,png|max:2048',
            'replace_images' => 'sometimes|array',
            'replace_images.*' => 'required|string',
            'remove_images' => 'sometimes|array',
            'remove_images.*' => 'required|string',
            'project_id' => [
                'sometimes', 'required',
                Rule::exists('tp_1_projects', 'id')->whereNull('deleted_at'),
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.string' => 'Nama kategori harus berupa teks.',
            'name.max' => 'Panjang nama kategori maksimal 255 karakter.',

            'value.required' => 'Nilai wajib diisi.',
            'value.numeric' => 'Nilai harus berupa angka.',

            'note.required' => 'Catatan wajib diisi.',
            'note.string' => 'Catatan harus berupa teks.',

            'images.array' => 'Gambar harus berupa array.',
            'images.*.file' => 'Setiap item harus berupa file.',
            'images.*.mimes' => 'Setiap file harus berupa gambar.',
            'images.*.max' => 'Ukuran file maksimal 2MB.',

            'replace_images.array' => 'Gambar harus berupa array.',
            'replace_images.*.required' => 'Setiap item harus diisi.',
            'replace_images.*.string' => 'Setiap item harus berupa string.',

            'remove_images.array' => 'Gambar harus berupa array.',
            'remove_images.*.required' => 'Setiap item harus diisi.',
            'remove_images.*.string' => 'Setiap item harus berupa string.',

            'project_id.required' => 'Proyek wajib dipilih.',
            'project_id.exists' => 'Proyek tidak ditemukan atau sudah dihapus.',
        ];
    }

    protected function prepareForValidation()
    {
        $data = [];

        if ($this->has('name')) {
            $data['name'] = strip_tags($this->name);
        }

        if ($this->has('value')) {
            $data['value'] = strip_tags($this->value);
        }

        if ($this->has('note')) {
            $data['note'] = strip_tags($this->note);
        }

        if ($this->has('project_id')) {
            $data['project_id'] = strip_tags($this->project_id);
        }

        $this->merge($data);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, Response::handler(
            400,
            'Failed to update activity doc category',
            [],
            [],
            $validator->errors()
        ));
    }
}
