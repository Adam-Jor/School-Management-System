<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $studentId = $this->route('student')?->id;

        return [
            'student_code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('tbl_students', 'student_code')->ignore($studentId),
            ],
            'name' => ['required', 'string', 'max:150'],
            'class_id' => ['required', 'exists:tbl_classes,id'],
            'user_id' => ['nullable', 'exists:tbl_users,id'],
            'phone' => ['nullable', 'regex:/^[0-9+\-\s]{9,16}$/'],
            'address' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::in(['Aktif', 'Pindah', 'Menunggu'])],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_code.required' => 'Kode siswa wajib diisi (contoh: STD-013).',
            'student_code.unique' => 'Kode siswa sudah terdaftar di sistem.',
            'name.required' => 'Nama lengkap siswa wajib diisi.',
            'class_id.required' => 'Kelas siswa wajib dipilih.',
            'class_id.exists' => 'Kelas yang dipilih tidak valid.',
            'phone.regex' => 'Format nomor telepon hanya boleh berisi angka (9–16 digit).',
            'status.required' => 'Status siswa wajib dipilih.',
            'photo.image' => 'File foto harus berupa gambar.',
            'photo.mimes' => 'Format foto harus JPG, PNG, atau WEBP.',
            'photo.max' => 'Ukuran foto maksimal 2 MB (2048 KB).',
        ];
    }
}
