<?php

namespace App\Imports;

use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;

class TeachersImport implements SkipsEmptyRows, ToCollection
{
    public int $imported = 0;

    public function collection(Collection $rows): void
    {
        $defaultSubject = Subject::first();

        foreach ($rows as $index => $row) {
            $firstCol = trim((string) ($row[0] ?? ''));
            if ($index === 0 && (stripos($firstCol, 'kode') !== false || stripos($firstCol, 'code') !== false)) {
                continue;
            }

            $code = $firstCol;
            $name = trim((string) ($row[1] ?? ''));
            $phone = trim((string) ($row[2] ?? '081200000000'));

            if ($code === '' || $name === '' || Teacher::withArchived()->where('teacher_code', $code)->exists()) {
                continue;
            }

            Teacher::create([
                'teacher_code' => $code,
                'name' => $name,
                'phone' => $phone,
                'subject_id' => $defaultSubject?->id,
                'status' => 'Aktif',
            ]);

            $this->imported++;
        }
    }
}
