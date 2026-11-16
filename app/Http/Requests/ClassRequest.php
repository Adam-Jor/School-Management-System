<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $classId = $this->route('schoolClass')?->id;

        return [
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('tbl_classes', 'code')->ignore($classId),
            ],
            'name' => ['required', 'string', 'max:100'],
            'capacity' => ['required', 'integer', 'min:10', 'max:60'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode kelas wajib diisi (contoh: CLS-10A3).',
            'code.unique' => 'Kode kelas sudah terdaftar.',
            'name.required' => 'Nama kelas wajib diisi (contoh: 10 IPA 3).',
            'capacity.required' => 'Kapasitas kursi kelas wajib diisi.',
        ];
    }
}
