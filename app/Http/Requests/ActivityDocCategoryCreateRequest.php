<?php

namespace App\Http\Requests;

use App\Helpers\ResponseHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ActivityDocCategoryCreateRequest extends FormRequest
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
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'name' => strip_tags($this->name),
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, ResponseHelper::error(
            400,
            'Failed to create activity document category',
            $validator->errors()
        ));
    }
}
