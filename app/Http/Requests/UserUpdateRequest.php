<?php

namespace App\Http\Requests;

use App\Helpers\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class UserUpdateRequest extends FormRequest
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
            'username'              => 'sometimes|required|string|max:100|unique:users,username,' . $this->route('id'),
            'name'                  => 'sometimes|required|string|max:255',
            'is_process'            => [
                'sometimes', 'required',
                function ($attribute, $value, $fail) {
                    if (!is_bool($value)) {
                        $fail('Status proses harus berupa boolean.');
                    }
                }
            ],
            'old_password'          => 'sometimes|required|required_with:new_password,confirm_new_password|string|max:255',
            'new_password'          => 'sometimes|required|required_with:old_password,confirm_new_password|string|max:255|different:old_password',
            'confirm_new_password'  => 'sometimes|required|required_with:old_password,new_password|string|max:255|same:new_password',
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Username wajib diisi.',
            'username.string' => 'Username harus berupa teks.',
            'username.max' => 'Username tidak boleh lebih dari 100 karakter.',
            'username.unique' => 'Username sudah digunakan.',

            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama tidak boleh lebih dari 255 karakter.',

            'is_process.required' => 'Status wajib diisi.',

            'old_password.required' => 'Password lama wajib diisi.',
            'old_password.string' => 'Password lama harus berupa teks.',
            'old_password.max' => 'Password lama tidak boleh lebih dari 255 karakter.',

            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.string' => 'Password baru harus berupa teks.',
            'new_password.max' => 'Password baru tidak boleh lebih dari 255 karakter.',
            'new_password.different' => 'Password baru tidak boleh sama dengan password lama.',

            'confirm_new_password.required' => 'Konfirmasi password baru wajib diisi.',
            'confirm_new_password.string' => 'Konfirmasi password baru harus berupa teks.',
            'confirm_new_password.max' => 'Konfirmasi password baru tidak boleh lebih dari 255 karakter.',
            'confirm_new_password.same' => 'Konfirmasi password baru harus sama dengan password baru.',
        ];
    }

    protected function prepareForValidation()
    {
        $data = [];

        if ($this->has('username')) {
            $data['username'] = trim($this->username);
        }

        if ($this->has('name')) {
            $data['name'] = trim($this->name);
        }

        $this->merge($data);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, Response::handler(
            400,
            'Gagal mengubah data pengguna',
            [],
            [],
            $validator->errors()
        ));
    }
}
