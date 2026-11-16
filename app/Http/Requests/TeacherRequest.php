<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $teacherId = $this->route('teacher')?->id;

        return [
            'teacher_code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('tbl_teachers', 'teacher_code')->ignore($teacherId),
            ],
            'name' => ['required', 'string', 'max:150'],
            'subject_id' => ['required', 'exists:tbl_subjects,id'],
            'class_ids' => ['nullable', 'array'],
            'class_ids.*' => ['integer', 'exists:tbl_classes,id'],
            'user_id' => ['nullable', 'exists:tbl_users,id'],
            'phone' => ['required', 'regex:/^[0-9+\-\s]{9,16}$/'],
            'status' => ['required', Rule::in(['Aktif', 'Cuti', 'Pindah'])],
            'notes' => ['nullable', 'string', 'max:500'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'teacher_code.required' => 'Kode guru wajib diisi (contoh: TCH-009).',
            'teacher_code.unique' => 'Kode guru sudah digunakan oleh guru lain.',
            'name.required' => 'Nama lengkap & gelar guru wajib diisi.',
            'subject_id.required' => 'Mata pelajaran yang diampu wajib dipilih.',
            'class_ids.*.exists' => 'Salah satu kelas yang diajar tidak ditemukan.',
            'phone.required' => 'Nomor telepon / WhatsApp guru wajib diisi.',
            'phone.regex' => 'Format nomor telepon hanya boleh berisi angka (9–16 digit).',
            'photo.mimes' => 'Format foto harus JPG, PNG, atau WEBP.',
            'photo.max' => 'Ukuran foto maksimal 2 MB (2048 KB).',
        ];
    }
}
