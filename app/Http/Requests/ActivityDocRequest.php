<?php

namespace App\Http\Requests;

use App\Helpers\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ActivityDocRequest extends FormRequest
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
            'title' => 'required|string|max:100',
            'file' => 'sometimes|file|mimes:pdf|max:2048',
            'description' => 'nullable|string',
            'tags' => 'required|array',
            'tags.*' => 'string',
            'activity_doc_category_id' => [
                'required',
                Rule::exists('activity_doc_categories', 'id')->whereNull('deleted_at'),
            ],
            'activity_id' => [
                'required',
                Rule::exists('activities', 'id')->whereNull('deleted_at'),
            ],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'title' => strip_tags($this->title),
            'description' => strip_tags($this->description),
            'tags' => is_string($this->tags) ? json_decode($this->tags, true) : $this->tags,
            'activity_doc_category_id' => strip_tags($this->activity_doc_category_id),
            'activity_id' => strip_tags($this->activity_id),
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, Response::handler(
            400,
            'Failed to create activity document',
            [],
            [],
            $validator->errors()
        ));
    }

    public function messages()
    {
        return [
            'file.max' => 'The uploaded file exceeds the maximum allowed size of 2MB.'
        ];
    }
}
