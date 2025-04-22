<?php

namespace App\Http\Requests;

use App\Helpers\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class CompanyUpdateRequest extends FormRequest
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
            'name' => 'sometimes|required|string|max:100',
            'address' => 'sometimes|required|string',
            'director_name' => 'sometimes|required|string|max:100',
            'director_phone' => 'sometimes|required|string|max:20',
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

            'director_phone.required' => 'Nomor telepon direktur wajib diisi.',
            'director_phone.string' => 'Nomor telepon direktur harus berupa teks.',
            'director_phone.max' => 'Nomor telepon direktur maksimal 20 karakter.',

            'director_signature.image' => 'Tanda tangan harus berupa gambar.',
            'director_signature.mimes' => 'Tanda tangan harus berformat jpeg, png, atau jpg.',
            'director_signature.max' => 'Ukuran tanda tangan maksimal 2MB.',
        ];
    }

    protected function prepareForValidation()
    {
        $data = [];

        if ($this->has('name')) {
            $data['name'] = strip_tags($this->name);
        }

        if ($this->has('address')) {
            $data['address'] = strip_tags($this->address);
        }

        if ($this->has('director_name')) {
            $data['director_name'] = strip_tags($this->director_name);
        }

        if ($this->has('director_phone')) {
            $data['director_phone'] = strip_tags($this->director_phone);
        }

        $this->merge($data);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, Response::handler(
            400,
            'Failed to update company',
            [],
            [],
            $validator->errors()
        ));
    }
}
