<?php

namespace App\Http\Requests;

use App\Helpers\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
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
            'old_password'          => 'sometimes|required|required_with:new_password,confirm_new_password|string|max:255',
            'new_password'          => 'sometimes|required|required_with:old_password,confirm_new_password|string|max:255|different:old_password',
            'confirm_new_password'  => 'sometimes|required|required_with:old_password,new_password|string|max:255|same:new_password',
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
            'Failed to update user',
            [],
            $validator->errors()
        ));
    }
}
