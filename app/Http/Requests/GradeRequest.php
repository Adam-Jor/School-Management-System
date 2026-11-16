<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:tbl_students,id'],
            'subject_id' => ['required', 'integer', 'exists:tbl_subjects,id'],
            'semester' => ['required', 'string', 'max:40'],
            'score' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'Siswa wajib dipilih.',
            'student_id.exists' => 'Data siswa yang dipilih tidak ditemukan.',
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'subject_id.exists' => 'Mata pelajaran yang dipilih tidak ditemukan.',
            'semester.required' => 'Semester wajib dipilih.',
            'semester.max' => 'Nama semester maksimal 40 karakter.',
            'score.required' => 'Nilai angka (0–100) wajib diisi.',
            'score.numeric' => 'Nilai harus berupa angka yang valid.',
            'score.min' => 'Nilai minimal adalah 0.',
            'score.max' => 'Nilai maksimal adalah 100.',
        ];
    }
}
