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
            'director_phone' => 'required|string|max:20',
            'director_signature' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'name' => strip_tags($this->name),
            'address' => strip_tags($this->address),
            'director_name' => strip_tags($this->director_name),
            'director_phone' => strip_tags($this->director_phone),
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, Response::handler(
            400,
            'Failed to create company',
            [],
            Arr::flatten(array_values($validator->errors()->toArray()))
        ));
    }
}
