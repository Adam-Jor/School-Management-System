<?php

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('indonesian_month_short returns exact short Indonesian month names and falls back safely', function () {
    $expected = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
        5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
        9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];

    foreach ($expected as $monthNumber => $shortName) {
        expect(indonesian_month_short($monthNumber))->toBe($shortName);
    }

    expect(indonesian_month_short(0))->toBe('Jan')
        ->and(indonesian_month_short(13))->toBe('Jan');
});

test('format_number handles null, zero, integers, and decimal formatting in Indonesian locale', function () {
    expect(format_number(null))->toBe('0')
        ->and(format_number(0))->toBe('0')
        ->and(format_number(1250000))->toBe('1.250.000')
        ->and(format_number(87.5, 2))->toBe('87,50');
});

test('format_date handles empty values, date strings, Carbon instances, and optional time suffix', function () {
    expect(format_date(null))->toBe('–')
        ->and(format_date(''))->toBe('–')
        ->and(format_date('2026-08-17'))->toBe('17 Agu 2026')
        ->and(format_date(Carbon::create(2026, 12, 5, 14, 30), true))->toBe('5 Des 2026 · 14:30');
});

test('edudash_dashboard_stats aggregates accurate counts, active flags, and monthly deltas from database', function () {
    Cache::forget('edudash_dashboard_stats');

    $lastMonth = now()->startOfMonth()->subMonth()->addDays(5);
    $thisMonth = now()->startOfMonth()->addDay();

    $classFilled = SchoolClass::create(['code' => 'CLS-1', 'name' => '10 IPA 1', 'capacity' => 36]);
    SchoolClass::create(['code' => 'CLS-2', 'name' => '10 IPA 2', 'capacity' => 36]);

    $subjectAssigned = Subject::create(['code' => 'MP-1', 'name' => 'Matematika', 'credit_hours' => 4]);
    Subject::create(['code' => 'MP-2', 'name' => 'Fisika', 'credit_hours' => 4]);

    $adminUser = User::factory()->create(['role' => 'admin']);
    $adminUser->created_at = $lastMonth;
    $adminUser->save();

    $teacherUser = User::factory()->create(['role' => 'teacher']);
    $teacherUser->created_at = $thisMonth;
    $teacherUser->save();

    $teacher = Teacher::create([
        'user_id' => $teacherUser->id,
        'subject_id' => $subjectAssigned->id,
        'teacher_code' => 'TCH-01',
        'name' => 'Guru Satu',
        'phone' => '081234567890',
        'status' => 'Aktif',
    ]);
    $teacher->created_at = $thisMonth;
    $teacher->save();

    $studentLastMonth = Student::create([
        'student_code' => 'STD-01',
        'name' => 'Siswa Lama',
        'class_id' => $classFilled->id,
        'status' => 'Aktif',
    ]);
    $studentLastMonth->created_at = $lastMonth;
    $studentLastMonth->save();

    $studentThisMonth = Student::create([
        'student_code' => 'STD-02',
        'name' => 'Siswa Baru',
        'class_id' => $classFilled->id,
        'status' => 'Menunggu',
    ]);
    $studentThisMonth->created_at = $thisMonth;
    $studentThisMonth->save();

    Cache::forget('edudash_dashboard_stats');
    $stats = edudash_dashboard_stats();

    expect($stats)->toMatchArray([
        'users' => 2,
        'users_this_month' => 1,
        'users_last_month' => 1,
        'students' => 2,
        'students_active' => 1,
        'students_this_month' => 1,
        'students_last_month' => 1,
        'teachers' => 1,
        'teachers_active' => 1,
        'teachers_this_month' => 1,
        'classes' => 2,
        'classes_filled' => 1,
        'subjects' => 2,
        'subjects_with_teachers' => 1,
    ]);
});

test('upload_image saves an uploaded image as webp in public uploads and returns relative path', function () {
    $file = UploadedFile::fake()->image('Foto Siswa Test.png', 200, 200);
    $relativePath = upload_image($file, 'unit_test_uploads');
    $absolutePath = public_path($relativePath);

    try {
        expect($relativePath)
            ->toStartWith('uploads/unit_test_uploads/')
            ->toEndWith('.webp')
            ->and(file_exists($absolutePath))->toBeTrue();
    } finally {
        if (file_exists($absolutePath)) {
            @unlink($absolutePath);
        }
        $dir = public_path('uploads/unit_test_uploads');
        if (is_dir($dir) && count(scandir($dir) ?: []) <= 2) {
            @rmdir($dir);
        }
    }
});
