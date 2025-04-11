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
