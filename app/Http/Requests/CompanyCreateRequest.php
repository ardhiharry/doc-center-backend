<?php

namespace App\Http\Requests;

use App\Helpers\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class CompanyCreateRequest extends FormRequest
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
            'name' => 'required|string|max:100',
            'address' => 'required|string',
            'director_name' => 'required|string|max:100',
            'established_date' => 'required|date',
            'director_signature' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama perusahaan wajib diisi.',
            'name.string' => 'Nama perusahaan harus berupa teks.',
            'name.max' => 'Nama perusahaan maksimal 100 karakter.',

            'address.required' => 'Alamat perusahaan wajib diisi.',
            'address.string' => 'Alamat perusahaan harus berupa teks.',

            'director_name.required' => 'Nama direktur wajib diisi.',
            'director_name.string' => 'Nama direktur harus berupa teks.',
            'director_name.max' => 'Nama direktur maksimal 100 karakter.',

            'director_signature.image' => 'Tanda tangan harus berupa gambar.',
            'director_signature.mimes' => 'Tanda tangan harus berformat jpeg, png, atau jpg.',
            'director_signature.max' => 'Ukuran tanda tangan maksimal 2MB.',

            'established_date.required' => 'Tanggal berdiri wajib diisi.',
            'established_date.date' => 'Tanggal berdiri harus berupa tanggal.',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'name' => strip_tags($this->name),
            'address' => strip_tags($this->address),
            'director_phone' => strip_tags($this->director_phone),
            'established_date' => strip_tags($this->established_date),
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, Response::handler(
            400,
            'Gagal membuat perusahaan',
            [],
            [],
            $validator->errors()
        ));
    }
}
