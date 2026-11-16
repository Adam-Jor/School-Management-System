<?php

namespace App\Imports;

use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;

class StudentsImport implements SkipsEmptyRows, ToCollection
{
    public int $imported = 0;

    public function collection(Collection $rows): void
    {
        $defaultClass = SchoolClass::first();

        foreach ($rows as $index => $row) {
            $firstCol = trim((string) ($row[0] ?? ''));
            if ($index === 0 && (stripos($firstCol, 'kode') !== false || stripos($firstCol, 'code') !== false)) {
                continue;
            }

            $code = $firstCol;
            $name = trim((string) ($row[1] ?? ''));
            $status = trim((string) ($row[2] ?? 'Aktif'));

            if ($code === '' || $name === '' || Student::withArchived()->where('student_code', $code)->exists()) {
                continue;
            }

            Student::create([
                'student_code' => $code,
                'name' => $name,
                'class_id' => $defaultClass?->id,
                'status' => in_array($status, ['Aktif', 'Pindah', 'Menunggu'], true) ? $status : 'Aktif',
            ]);

            $this->imported++;
        }
    }
}
