<?php

namespace App\Http\Requests;

use App\Helpers\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminDocRequest extends FormRequest
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
            'project_id' => 'required|exists:projects,id',
            'admin_doc_category_id' => [
                'required',
                Rule::exists('admin_doc_categories', 'id')->whereNull('deleted_at'),
            ],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'title' => strip_tags($this->title),
            'project_id' => strip_tags($this->project_id),
            'admin_doc_category_id' => strip_tags($this->admin_doc_category_id),
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, Response::handler(
            400,
            'Failed to create admin document',
            [],
            [],
            $validator->errors()
        ));
    }
}
