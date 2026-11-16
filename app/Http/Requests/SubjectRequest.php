<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $subjectId = $this->route('subject')?->id;

        return [
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('tbl_subjects', 'code')->ignore($subjectId),
            ],
            'name' => ['required', 'string', 'max:150'],
            'credit_hours' => ['required', 'integer', 'min:1', 'max:12'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode mata pelajaran wajib diisi (contoh: MP-009).',
            'code.unique' => 'Kode mata pelajaran sudah terdaftar.',
            'name.required' => 'Nama mata pelajaran wajib diisi.',
            'credit_hours.required' => 'Jumlah jam pelajaran (JP) wajib diisi.',
        ];
    }
}
