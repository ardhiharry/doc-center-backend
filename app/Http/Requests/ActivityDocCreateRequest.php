<?php

namespace App\Http\Requests;

use App\Helpers\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Mews\Purifier\Facades\Purifier;

class ActivityDocCreateRequest extends FormRequest
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
            'date' => 'required|date',
            'location' => 'required|string|max:255',
            'meet_of_person' => 'required|array',
            'meet_of_person.*' => 'required|string|max:255',
            'agenda' => 'required|array',
            'agenda.*' => 'required|string|max:255',
            'activity' => 'required|array',
            'activity.*' => 'required|string|max:255',
            'files' => 'sometimes|array',
            'files.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png|max:2048',
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

            'tags.required' => 'Tag wajib diisi.',
            'tags.array' => 'Tag harus berupa array.',
            'tags.*.string' => 'Tag harus berupa teks.',

            'activity_id.required' => 'Aktivitas wajib dipilih.',
            'activity_id.exists' => 'Aktivitas tidak ditemukan.',
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
