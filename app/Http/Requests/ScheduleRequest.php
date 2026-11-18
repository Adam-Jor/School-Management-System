<?php

namespace App\Http\Requests;

use App\Models\Schedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'class_id' => ['required', 'exists:tbl_classes,id'],
            'subject_id' => ['required', 'exists:tbl_subjects,id'],
            'teacher_id' => ['required', 'exists:tbl_teachers,id'],
            'academic_period_id' => ['nullable', 'exists:tbl_academic_periods,id'],
            'day_of_week' => ['required', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'room' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $currentId = $this->route('schedule') instanceof Schedule
                ? $this->route('schedule')->id
                : $this->route('schedule');

            $day = $this->input('day_of_week');
            $start = $this->input('start_time');
            $end = $this->input('end_time');
            $teacherId = $this->input('teacher_id');
            $classId = $this->input('class_id');

            // 1. Cek bentrok jadwal guru
            $teacherConflict = Schedule::where('day_of_week', $day)
                ->where('teacher_id', $teacherId)
                ->when($currentId, fn ($q) => $q->where('id', '!=', $currentId))
                ->where(function ($q) use ($start, $end) {
                    $q->where(fn ($sub) => $sub->where('start_time', '<', $end)->where('end_time', '>', $start));
                })
                ->exists();

            if ($teacherConflict) {
                $validator->errors()->add('teacher_id', 'Guru sudah memiliki jadwal mengajar di hari dan rentang jam tersebut.');
            }

            // 2. Cek bentrok jadwal kelas
            $classConflict = Schedule::where('day_of_week', $day)
                ->where('class_id', $classId)
                ->when($currentId, fn ($q) => $q->where('id', '!=', $currentId))
                ->where(function ($q) use ($start, $end) {
                    $q->where(fn ($sub) => $sub->where('start_time', '<', $end)->where('end_time', '>', $start));
                })
                ->exists();

            if ($classConflict) {
                $validator->errors()->add('class_id', 'Kelas rombel ini sudah memiliki jadwal pelajaran lain pada jam tersebut.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'class_id.required' => 'Rombongan belajar wajib dipilih.',
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'teacher_id.required' => 'Guru pengajar wajib dipilih.',
            'day_of_week.required' => 'Hari wajib dipilih.',
            'start_time.required' => 'Jam mulai wajib diisi.',
            'end_time.required' => 'Jam selesai wajib diisi.',
            'end_time.after' => 'Jam selesai harus lebih akhir dari jam mulai.',
        ];
    }
}
