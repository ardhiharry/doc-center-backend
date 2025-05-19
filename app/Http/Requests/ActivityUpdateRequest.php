<?php

namespace App\Http\Requests;

use App\Helpers\Response;
use App\Models\Activity;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
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
            'title' => 'sometimes|required|string|max:100',
            'status' => 'sometimes|required|in:ON PROGRESS,DONE',
            'start_date' => 'sometimes|required|date|before_or_equal:end_date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'project_id' => [
                'sometimes', 'required',
                Rule::exists('tp_1_projects', 'id')->whereNull('deleted_at'),
            ],
            'author_id' => [
                'sometimes', 'required',
                Rule::exists('tm_users', 'id')->whereNull('deleted_at'),
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul wajib diisi.',
            'title.string' => 'Judul harus berupa teks.',
            'title.max' => 'Judul tidak boleh lebih dari 100 karakter.',

            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status tidak valid. Status yang valid adalah ON PROGRESS, DONE.',

            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'start_date.date' => 'Tanggal mulai harus berupa tanggal.',
            'start_date.before_or_equal' => 'Tanggal mulai harus sebelum atau sama dengan tanggal selesai.',

            'end_date.required' => 'Tanggal selesai wajib diisi.',
            'end_date.date' => 'Tanggal selesai harus berupa tanggal.',
            'end_date.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',

            'project_id.required' => 'Proyek wajib dipilih.',
            'project_id.exists' => 'Proyek tidak ditemukan.',

            'author_id.required' => 'Author wajib dipilih.',
            'author_id.exists' => 'Author tidak ditemukan.',
        ];
    }

    protected function prepareForValidation()
    {
        $data = [];

        if ($this->has('title')) {
            $data['title'] = strip_tags($this->title);
        }

        if ($this->has('status')) {
            $data['status'] = strip_tags($this->status);
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

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $activityId = $this->route('id');
            $activity = Activity::find($activityId);

            if (!$activity) {
                return;
            }

            $startDate = $this->input('start_date', $activity->start_date);
            $endDate = $this->input('end_date', $activity->end_date);

            if ($startDate && $endDate && $startDate > $endDate) {
                $validator->errors()->add('start_date', 'The start date must be a date before or equal to end date.');
            }

            if ($startDate && $endDate && $endDate < $startDate) {
                $validator->errors()->add('end_date', 'The end date must be a date after or equal to start date.');
            }
        });
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, Response::handler(
            400,
            'Gagal mengubah aktivitas',
            [],
            [],
            $validator->errors()
        ));
    }
}
