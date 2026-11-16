<?php

namespace App\Exports;

use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudentsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(
        protected string $search = '',
        protected string $classId = '',
        protected string $status = '',
    ) {}

    public function query(): Builder
    {
        return Student::query()
            ->select('tbl_students.*')
            ->leftJoin('tbl_classes', 'tbl_students.class_id', '=', 'tbl_classes.id')
            ->leftJoin('tbl_users', 'tbl_students.user_id', '=', 'tbl_users.id')
            ->with(['schoolClass', 'user'])
            ->when($this->search !== '', function ($q) {
                $q->where(function ($sub) {
                    $sub->where('tbl_students.name', 'like', "%{$this->search}%")
                        ->orWhere('tbl_students.student_code', 'like', "%{$this->search}%")
                        ->orWhere('tbl_users.email', 'like', "%{$this->search}%")
                        ->orWhere('tbl_classes.name', 'like', "%{$this->search}%");
                });
            })
            ->when($this->classId !== '', fn ($q) => $q->where('tbl_students.class_id', $this->classId))
            ->when($this->status !== '', fn ($q) => $q->where('tbl_students.status', $this->status))
            ->orderByDesc('tbl_students.id');
    }

    public function headings(): array
    {
        return ['Kode Siswa', 'Nama Lengkap', 'Kelas', 'Email Akun', 'No. Telepon', 'Status', 'Tanggal Terdaftar'];
    }

    public function map($student): array
    {
        return [
            $student->student_code,
            $student->name,
            $student->schoolClass?->name ?? '-',
            $student->user?->email ?? '-',
            $student->phone ?? '-',
            $student->status,
            format_date($student->created_at),
        ];
    }
}
