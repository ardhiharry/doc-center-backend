<?php

namespace App\Http\Requests;

use App\Helpers\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TeamUpdateRequest extends FormRequest
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
            'project_id' => [
                'sometimes', 'required',
                Rule::exists('projects', 'id')->whereNull('deleted_at'),
            ],
            'user_id' => [
                'sometimes', 'required',
                Rule::exists('users', 'id')->whereNull('deleted_at'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'project_id.required' => 'Project wajib dipilih.',
            'project_id.exists' => 'Project tidak ditemukan atau sudah dihapus.',

            'user_id.required' => 'User wajib dipilih.',
            'user_id.exists' => 'User tidak ditemukan atau sudah dihapus.',
        ];
    }

    protected function prepareForValidation()
    {
        $data = [];

        if ($this->has('project_id')) {
            $data['project_id'] = strip_tags($this->project_id);
        }

        if ($this->has('user_id')) {
            $data['user_id'] = strip_tags($this->user_id);
        }

        $this->merge($data);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, Response::handler(
            400,
            'Failed to update team',
            [],
            [],
            $validator->errors()
        ));
    }
}
