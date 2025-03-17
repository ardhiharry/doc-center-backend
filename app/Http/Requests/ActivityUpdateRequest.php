<?php

namespace App\Http\Requests;

use App\Helpers\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class ActivityUpdateRequest extends FormRequest
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
            'title' => 'sometimes|string|max:100',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date',
            'project_id' => 'sometimes|exists:projects,id',
        ];
    }

    protected function prepareForValidation()
    {
        $data = [];

        if ($this->has('title')) {
            $data['title'] = strip_tags($this->title);
        }

        if ($this->has('start_date')) {
            $data['start_date'] = strip_tags($this->start_date);
        }

        if ($this->has('end_date')) {
            $data['end_date'] = strip_tags($this->end_date);
        }

        if ($this->has('project_id')) {
            $data['project_id'] = strip_tags($this->project_id);
        }

        $this->merge($data);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, Response::handler(
            400,
            'Failed to update activity',
            [],
            $validator->errors()
        ));
    }
}
