<?php

namespace App\Http\Requests;

use App\Helpers\Response;
use App\Models\Project;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
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
            'code' => 'sometimes|required|string|max:10',
            'contract_number' => 'sometimes|required|string|max:100',
            'contract_date' => 'sometimes|required|date',
            'client' => 'sometimes|required|string|max:100',
            'ppk' => 'sometimes|required|string|max:100',
            'support_teams' => 'sometimes|required|array',
            'support_teams.*' => 'string',
            'value' => [
                'sometimes', 'required',
                function ($attribute, $value, $fail) {
                    if (filter_var($value, FILTER_VALIDATE_INT) === false) {
                        $fail('Nilai harus berupa angka.');
                    }

                    if ($value <= 0) {
                        $fail('Nilai tidak boleh negatif atau nol.');
                    }
                }
            ],
            'status' => 'sometimes|required|in:WAITING,ON PROGRESS,CLOSED',
            'progress' => [
                'sometimes', 'required',
                function ($attribute, $value, $fail) {
                    if (!is_int($value)) {
                        $fail('Progress harus berupa angka.');
                    }

                    if ($value < 0 || $value > 100) {
                        $fail('Progress harus berada di rentang 0-100.');
                    }
                },
                'between:0,100'
            ],
            'company_id' => [
                'sometimes', 'required',
                Rule::exists('tm_companies', 'id')->whereNull('deleted_at'),
            ],
            'project_leader_id' => [
                'sometimes', 'required',
                Rule::exists('tm_users', 'id')->whereNull('deleted_at'),
            ],
            'start_date' => 'sometimes|required|date|before_or_equal:end_date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'maintenance_date' => 'sometimes|required|date',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama maksimal 100 karakter.',

            'code.required' => 'Kode wajib diisi.',
            'code.string' => 'Kode harus berupa teks.',
            'code.max' => 'Kode maksimal 10 karakter.',

            'contract_number.required' => 'Nomor kontrak wajib diisi.',
            'contract_number.string' => 'Nomor kontrak harus berupa teks.',
            'contract_number.max' => 'Nomor kontrak maksimal 100 karakter.',

            'contract_date.required' => 'Tanggal kontrak wajib diisi.',
            'contract_date.date' => 'Tanggal kontrak harus berupa tanggal.',

            'client.required' => 'Klien wajib diisi.',
            'client.string' => 'Klien harus berupa teks.',
            'client.max' => 'Klien maksimal 100 karakter.',

            'ppk.required' => 'PPK wajib diisi.',
            'ppk.string' => 'PPK harus berupa teks.',
            'ppk.max' => 'PPK maksimal 100 karakter.',

            'support_teams.required' => 'Tim dukungan wajib diisi.',
            'support_teams.array' => 'Tim dukungan harus berupa array.',
            'support_teams.*.string' => 'Tim dukungan harus berupa teks.',

            'value.required' => 'Nilai wajib diisi.',

            'status.required' => 'Status wajib diisi.',
            'status.in' => 'Status tidak valid. Status yang valid: WAITING, ON PROGRESS, CLOSED.',

            'progress.required' => 'Progres wajib diisi.',
            'progress.between' => 'Progres harus antara 0 dan 100.',

            'company_id.required' => 'Perusahaan wajib dipilih.',
            'company_id.exists' => 'Perusahaan tidak ditemukan.',

            'project_leader_id.required' => 'Pemimpin proyek wajib dipilih.',
            'project_leader_id.exists' => 'Pemimpin proyek tidak ditemukan.',

            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'start_date.date' => 'Tanggal mulai harus berupa tanggal yang valid.',
            'start_date.before_or_equal' => 'Tanggal mulai harus sebelum atau sama dengan tanggal selesai.',

            'end_date.required' => 'Tanggal selesai wajib diisi.',
            'end_date.date' => 'Tanggal selesai harus berupa tanggal yang valid.',
            'end_date.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',

            'maintenance_date.required' => 'Tanggal pemeliharaan wajib diisi.',
            'maintenance_date.date' => 'Tanggal pemeliharaan harus berupa tanggal yang valid.',
        ];
    }

    protected function prepareForValidation()
    {
        $data = [];

        if ($this->has('name')) {
            $data['name'] = strip_tags($this->name);
        }

        if ($this->has('code')) {
            $data['code'] = strip_tags($this->code);
        }

        if ($this->has('contract_number')) {
            $data['contract_number'] = strip_tags($this->contract_number);
        }

        if ($this->has('contract_date')) {
            $data['contract_date'] = strip_tags($this->contract_date);
        }

        if ($this->has('client')) {
            $data['client'] = strip_tags($this->client);
        }

        if ($this->has('ppk')) {
            $data['ppk'] = strip_tags($this->ppk);
        }

        if ($this->has('support_teams')) {
            $data['support_teams'] = is_array($this->support_teams)
            ? array_map('strip_tags', $this->support_teams)
            : strip_tags($this->support_teams);
        }

        if ($this->has('value')) {
            $data['value'] = strip_tags($this->value);
        }

        if ($this->has('status')) {
            $data['status'] = strip_tags($this->status);
        }

        if ($this->has('company_id')) {
            $data['company_id'] = strip_tags($this->company_id);
        }

        if ($this->has('project_leader_id')) {
            $data['project_leader_id'] = strip_tags($this->project_leader_id);
        }

        if ($this->has('start_date')) {
            $data['start_date'] = strip_tags($this->start_date);
        }

        if ($this->has('end_date')) {
            $data['end_date'] = strip_tags($this->end_date);
        }

        if ($this->has('maintenance_date')) {
            $data['maintenance_date'] = strip_tags($this->maintenance_date);
        }

        $this->merge($data);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, Response::handler(
            400,
            'Gagal memperbarui proyek',
            [],
            [],
            $validator->errors()
        ));
    }
}
