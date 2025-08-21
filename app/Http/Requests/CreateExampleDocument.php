<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateExampleDocument extends FormRequest
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
            "title" => ["required", "string", "max:255"],
            "files" => ["required", "array"],
            "files.*" => ["required", "file", "mimes:pdf,doc,docx,ppt,pptx,xls,xlsx"],
        ];
    }

    public function messages()
    {
        return [
            "title.required" => "Judul wajib diisi.",
            "title.string" => "Judul harus berupa teks.",
            "title.max" => "Judul tidak boleh lebih dari 255 karakter.",

            "files.required" => "File wajib diisi.",
            "files.array" => "File harus berupa array.",
            "files.*.required" => "File wajib diisi.",
            "files.*.file" => "File harus berupa file.",
            "files.*.mimes" => "File harus berupa PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX.",
        ];
    }
}
