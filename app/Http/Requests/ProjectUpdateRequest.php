<?php

namespace App\Http\Requests;

use App\Helpers\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
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
            'name' => 'sometimes|required|string|max:100',
            'company_id' => 'sometimes|required|exists:companies,id',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date',
        ];
    }

    protected function prepareForValidation()
    {
        $data = [];

        if ($this->has('name')) {
            $data['name'] = strip_tags($this->name);
        }

        if ($this->has('company_id')) {
            $data['company_id'] = strip_tags($this->company_id);
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
        throw new ValidationException($validator, Response::handler(
            400,
            'Failed to update project',
            [],
            $validator->errors()
        ));
    }
}
