<?php

namespace App\Http\Requests;

use App\Helpers\ResponseHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ProjectUpdateRequest extends FormRequest
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
            'project_name' => 'sometimes|required|string|max:100',
            'company_name' => 'sometimes|required|string|max:100',
            'company_address' => 'sometimes|required|string',
            'director_name' => 'sometimes|required|string|max:100',
            'director_phone' => 'sometimes|required|string|max:20',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date',
        ];
    }

    protected function prepareForValidation()
    {
        $data = [];

        if ($this->has('project_name')) {
            $data['project_name'] = strip_tags($this->project_name);
        }

        if ($this->has('company_name')) {
            $data['company_name'] = strip_tags($this->company_name);
        }

        if ($this->has('company_address')) {
            $data['company_address'] = strip_tags($this->company_address);
        }

        if ($this->has('director_name')) {
            $data['director_name'] = strip_tags($this->director_name);
        }

        if ($this->has('director_phone')) {
            $data['director_phone'] = strip_tags($this->director_phone);
        }

        if ($this->has('start_date')) {
            $data['start_date'] = strip_tags($this->start_date);
        }

        if ($this->has('end_date')) {
            $data['end_date'] = strip_tags($this->end_date);
        }

        $this->merge($data);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, ResponseHelper::error(
            400,
            'Failed to update project',
            $validator->errors()
        ));
    }
}
