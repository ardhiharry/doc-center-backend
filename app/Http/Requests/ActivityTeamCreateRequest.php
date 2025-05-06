<?php

namespace App\Http\Requests;

use App\Helpers\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ActivityTeamCreateRequest extends FormRequest
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
            'activity_id' => [
                'required',
                Rule::exists('tp_4_activities', 'id')->whereNull('deleted_at'),
            ],
            'user_id' => [
                'required',
                Rule::exists('tm_users', 'id')->whereNull('deleted_at'),
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'activity_id.required' => 'Aktivitas wajib dipilih',
            'activity_id.exists' => 'Aktivitas tidak ditemukan',

            'user_id.required' => 'User wajib dipilih',
            'user_id.exists' => 'User tidak ditemukan',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'activity_id' => strip_tags($this->activity_id),
            'user_id' => strip_tags($this->user_id),
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, Response::handler(
            400,
            'Gagal membuat tim aktivitas',
            [],
            [],
            $validator->errors()
        ));
    }
}
