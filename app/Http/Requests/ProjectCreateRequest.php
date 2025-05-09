<?php

namespace App\Http\Requests;

use App\Helpers\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProjectCreateRequest extends FormRequest
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
            'code' => 'required|string|max:10',
            'client' => 'required|string|max:100',
            'ppk' => 'required|string|max:100',
            'support_teams' => 'required|array',
            'support_teams.*' => 'string',
            'value' => 'required|numeric',
            'company_id' => [
                'required',
                Rule::exists('tm_companies', 'id')->whereNull('deleted_at'),
            ],
            'project_leader_id' => [
                'required',
                Rule::exists('tm_users', 'id')->whereNull('deleted_at'),
            ],
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'maintenance_date' => 'required|date',
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

            'client.required' => 'Klien wajib diisi.',
            'client.string' => 'Klien harus berupa teks.',
            'client.max' => 'Klien maksimal 100 karakter.',

            'ppk.required' => 'PPK wajib diisi.',
            'ppk.string' => 'PPK harus berupa teks.',
            'ppk.max' => 'PPK maksimal 100 karakter.',

            'support_teams.required' => 'Tim support wajib diisi.',
            'support_teams.array' => 'Tim support harus berupa array.',
            'support_teams.*.string' => 'Tim support harus berupa teks.',

            'value.required' => 'Nilai wajib diisi.',
            'value.numeric' => 'Nilai harus berupa angka.',

            'company_id.required' => 'Perusahaan wajib dipilih.',
            'company_id.exists' => 'Perusahaan tidak ditemukan atau sudah dihapus.',

            'project_leader_id.required' => 'Pemimpin proyek wajib dipilih.',
            'project_leader_id.exists' => 'Pemimpin proyek tidak ditemukan atau sudah dihapus.',

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
        $this->merge([
            'name' => strip_tags($this->name),
            'code' => strip_tags($this->code),
            'client' => strip_tags($this->client),
            'ppk' => strip_tags($this->ppk),
            'support_teams' => is_string($this->support_teams) ? json_decode($this->support_teams, true) : $this->support_teams,
            'value' => strip_tags($this->value),
            'company_id' => strip_tags($this->company_id),
            'project_leader_id' => strip_tags($this->project_leader_id),
            'start_date' => strip_tags($this->start_date),
            'end_date' => strip_tags($this->end_date),
            'maintenance_date' => strip_tags($this->maintenance_date),
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, Response::handler(
            400,
            'Gagal membuat proyek',
            [],
            [],
            $validator->errors()
        ));
    }
}
