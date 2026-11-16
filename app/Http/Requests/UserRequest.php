<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => ['required', 'string', 'max:150'],
            'username' => [
                'required',
                'string',
                'max:60',
                Rule::unique('tbl_users', 'username')->ignore($userId),
            ],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('tbl_users', 'email')->ignore($userId),
            ],
            'password' => [$userId ? 'nullable' : 'required', 'string', 'min:6'],
            'phone' => ['nullable', 'regex:/^[0-9+\-\s]{9,16}$/'],
            'role' => ['required', Rule::in(['admin', 'teacher', 'student'])],
            'status' => ['required', Rule::in(['Aktif', 'Nonaktif'])],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap pengguna wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan pengguna lain.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email sudah terdaftar.',
            'password.required' => 'Kata sandi wajib diisi untuk pengguna baru.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'phone.regex' => 'Format nomor telepon hanya boleh berisi angka (9–16 digit).',
        ];
    }
}
