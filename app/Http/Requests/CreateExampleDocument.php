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
            "file" => ["required", "string"],
        ];
    }

    public function messages()
    {
        return [
            "title.required" => "Judul wajib diisi.",
            "title.string" => "Judul harus berupa teks.",
            "title.max" => "Judul tidak boleh lebih dari 255 karakter.",

            "file.required" => "File wajib diisi.",
            "file.string" => "File harus berupa teks.",
        ];
    }
}
