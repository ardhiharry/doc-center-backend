<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExampleDocument extends FormRequest
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
            "title" => ["sometimes", "string", "max:255"],
            "files" => ["sometimes", "string"],
        ];
    }

    public function messages()
    {
        return [
            "title.string" => "Judul harus berupa teks.",
            "title.max" => "Judul tidak boleh lebih dari 255 karakter.",

            "files.string" => "File harus berupa teks.",
        ];
    }
}
