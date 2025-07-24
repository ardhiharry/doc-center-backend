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
            'date' => 'sometimes|required|date',
            'location' => 'sometimes|required|string|max:255',
            'meet_of_person' => 'sometimes|required|array',
            'meet_of_person.*' => 'required|string|max:255',
            'agenda' => 'sometimes|required|array',
            'agenda.*' => 'required|string|max:255',
            'activity' => 'sometimes|required|array',
            'activity.*' => 'required|string|max:255',
            'files' => 'sometimes|array',
            'files.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png|max:2048',
            'replace_files' => 'sometimes|array',
            'replace_files.*' => 'required|string',
            'remove_files' => 'sometimes|array',
            'remove_files.*' => 'required|string',
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

            'date.required' => 'Tanggal wajib diisi.',
            'date.date' => 'Tanggal harus berupa tanggal.',

            'location.required' => 'Lokasi wajib diisi.',
            'location.string' => 'Lokasi harus berupa teks.',
            'location.max' => 'Lokasi tidak boleh lebih dari 255 karakter.',

            'meet_of_person.required' => 'Pertemuan wajib diisi.',
            'meet_of_person.array' => 'Pertemuan harus berupa array.',
            'meet_of_person.*.string' => 'Setiap item harus berupa teks.',
            'meet_of_person.*.max' => 'Setiap teks tidak boleh lebih dari 255 karakter.',

            'agenda.required' => 'Agenda wajib diisi.',
            'agenda.array' => 'Agenda harus berupa array.',
            'agenda.*.string' => 'Setiap item harus berupa teks.',
            'agenda.*.max' => 'Setiap teks tidak boleh lebih dari 255 karakter.',

            'activity.required' => 'Aktivitas wajib diisi.',
            'activity.array' => 'Aktivitas harus berupa array.',
            'activity.*.string' => 'Setiap item harus berupa teks.',
            'activity.*.max' => 'Setiap teks tidak boleh lebih dari 255 karakter.',

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
