<?php

namespace App\Exports;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TeachersExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(
        protected string $search = '',
        protected string $subjectId = '',
        protected string $status = '',
    ) {}

    public function query(): Builder
    {
        return Teacher::query()
            ->select('tbl_teachers.*')
            ->leftJoin('tbl_subjects', 'tbl_teachers.subject_id', '=', 'tbl_subjects.id')
            ->leftJoin('tbl_users', 'tbl_teachers.user_id', '=', 'tbl_users.id')
            ->with(['subject', 'user'])
            ->when($this->search !== '', function ($q) {
                $q->where(function ($sub) {
                    $sub->where('tbl_teachers.name', 'like', "%{$this->search}%")
                        ->orWhere('tbl_teachers.teacher_code', 'like', "%{$this->search}%")
                        ->orWhere('tbl_teachers.phone', 'like', "%{$this->search}%")
                        ->orWhere('tbl_subjects.name', 'like', "%{$this->search}%");
                });
            })
            ->when($this->subjectId !== '', fn ($q) => $q->where('tbl_teachers.subject_id', $this->subjectId))
            ->when($this->status !== '', fn ($q) => $q->where('tbl_teachers.status', $this->status))
            ->orderBy('tbl_teachers.name');
    }

    public function headings(): array
    {
        return ['Kode Guru', 'Nama Guru', 'Mata Pelajaran', 'Email Akun', 'No. Telepon', 'Status', 'Tanggal Terdaftar'];
    }

    public function map($teacher): array
    {
        return [
            $teacher->teacher_code,
            $teacher->name,
            $teacher->subject?->name ?? '-',
            $teacher->user?->email ?? '-',
            $teacher->phone ?? '-',
            $teacher->status,
            format_date($teacher->created_at),
        ];
    }
}
