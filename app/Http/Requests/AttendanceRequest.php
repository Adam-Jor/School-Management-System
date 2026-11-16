<?php

namespace App\Http\Requests;

use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isTeacher()) {
            $classId = (int) $this->input('class_id');
            $teacher = $user->teacher;

            if (! $teacher || $classId <= 0) {
                return false;
            }

            return $teacher->schoolClasses()
                ->where('tbl_classes.id', $classId)
                ->exists();
        }

        return false;
    }

    public function rules(): array
    {
        return [
            'class_id' => ['required', 'integer', 'exists:tbl_classes,id'],
            'date' => ['required', 'date'],
            'statuses' => ['required', 'array', 'min:1'],
            'statuses.*' => ['required', Rule::in(['Hadir', 'Izin', 'Sakit', 'Alpa'])],
            'notes' => ['nullable', 'array'],
            'notes.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $classId = (int) $this->input('class_id');
            $statuses = $this->input('statuses');

            if ($classId <= 0 || ! is_array($statuses) || empty($statuses)) {
                return;
            }

            $submittedStudentIds = array_map('intval', array_keys($statuses));
            $validCount = Student::query()
                ->where('class_id', $classId)
                ->whereIn('id', $submittedStudentIds)
                ->count();

            if ($validCount !== count(array_unique($submittedStudentIds))) {
                $validator->errors()->add('statuses', 'Terdapat siswa yang bukan anggota kelas yang dipilih.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'class_id.required' => 'Kelas absensi wajib dipilih.',
            'class_id.exists' => 'Kelas yang dipilih tidak ditemukan.',
            'date.required' => 'Tanggal absensi wajib diisi.',
            'date.date' => 'Format tanggal absensi tidak valid.',
            'statuses.required' => 'Data status kehadiran siswa wajib diisi.',
            'statuses.min' => 'Minimal satu data kehadiran siswa harus diisi.',
            'statuses.*.in' => 'Status kehadiran harus Hadir, Izin, Sakit, atau Alpa.',
            'notes.*.max' => 'Catatan keterangan siswa maksimal 255 karakter.',
        ];
    }
}
