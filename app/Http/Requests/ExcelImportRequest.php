<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExcelImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'excel_file' => ['required_without:csv_file', 'nullable', 'file', 'mimes:xlsx,xls,csv,txt', 'max:4096'],
            'csv_file' => ['nullable', 'file', 'mimes:xlsx,xls,csv,txt', 'max:4096'],
        ];
    }

    public function messages(): array
    {
        return [
            'excel_file.required_without' => 'Pilih file Excel (.xlsx) atau CSV untuk diimpor.',
            'excel_file.mimes' => 'File harus berformat XLSX, XLS, atau CSV.',
            'excel_file.max' => 'Ukuran file impor maksimal 4 MB.',
            'csv_file.mimes' => 'File harus berformat XLSX, XLS, atau CSV.',
            'csv_file.max' => 'Ukuran file impor maksimal 4 MB.',
        ];
    }
}
