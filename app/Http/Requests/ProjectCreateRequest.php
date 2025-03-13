<?php

namespace App\Http\Requests;

use App\Helpers\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ProjectCreateRequest extends FormRequest
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
            'project_name' => 'required|string|max:100',
            'company_name' => 'required|string|max:100',
            'company_address' => 'required|string',
            'director_name' => 'required|string|max:100',
            'director_phone' => 'required|string|max:20',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'project_name' => strip_tags($this->project_name),
            'company_name' => strip_tags($this->company_name),
            'company_address' => strip_tags($this->company_address),
            'director_name' => strip_tags($this->director_name),
            'director_phone' => strip_tags($this->director_phone),
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, Response::handler(
            400,
            'Failed to create project',
            [],
            $validator->errors()
        ));
    }
}
