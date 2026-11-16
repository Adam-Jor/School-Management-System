<?php

use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('user model checks roles, generates initials and slug, hashes password, and relates student/teacher', function () {
    $admin = User::factory()->create(['name' => 'Administrator Utama', 'role' => 'admin', 'password' => 'secret123']);
    $teacherUser = User::factory()->create(['name' => 'Budi', 'role' => 'teacher']);
    $studentUser = User::factory()->create(['name' => '   ', 'role' => 'student']);

    expect($admin->isAdmin())->toBeTrue()
        ->and($admin->isTeacher())->toBeFalse()
        ->and($admin->isStudent())->toBeFalse()
        ->and($teacherUser->isTeacher())->toBeTrue()
        ->and($studentUser->isStudent())->toBeTrue()
        ->and($admin->initials())->toBe('AU')
        ->and($teacherUser->initials())->toBe('B')
        ->and($studentUser->initials())->toBe('ED')
        ->and($admin->slug)->toStartWith('administrator-utama-')
        ->and(Hash::check('secret123', $admin->password))->toBeTrue()
        ->and($admin->toArray())->not->toHaveKeys(['password', 'remember_token']);
});

test('teacher, student, school class, and subject auto-generate slugs and maintain domain relationships', function () {
    $subject = Subject::create(['code' => 'MP-10', 'name' => 'Fisika Terapan', 'credit_hours' => 4]);
    $classA = SchoolClass::create(['code' => 'CLS-10A', 'name' => '10 IPA 1', 'capacity' => 36]);
    $classB = SchoolClass::create(['code' => 'CLS-10B', 'name' => '10 IPA 2', 'capacity' => 34]);

    $teacherUser = User::factory()->create(['role' => 'teacher']);
    $teacher = Teacher::create([
        'user_id' => $teacherUser->id,
        'subject_id' => $subject->id,
        'teacher_code' => 'TCH-010',
        'name' => 'Budi Santoso, S.Pd',
        'phone' => '081234567890',
        'status' => 'Aktif',
    ]);
    $teacher->schoolClasses()->sync([$classA->id, $classB->id]);

    $studentUser = User::factory()->create(['role' => 'student']);
    $student = Student::create([
        'user_id' => $studentUser->id,
        'class_id' => $classA->id,
        'student_code' => 'STD-010',
        'name' => 'Alya Putri Ramadhani',
        'status' => 'Aktif',
    ]);

    $attendance = Attendance::create([
        'student_id' => $student->id,
        'class_id' => $classA->id,
        'date' => '2026-11-16',
        'status' => 'Hadir',
        'notes' => 'Tepat waktu',
    ]);

    $grade = Grade::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'semester' => 'Ganjil 2026/2027',
        'score' => 94.5,
        'predicate' => 'A',
    ]);

    expect($subject->slug)->toBe('mp-10-fisika-terapan')
        ->and($classA->slug)->toBe('cls-10a-10-ipa-1')
        ->and($teacher->slug)->toBe('tch-010-budi-santoso-spd')
        ->and($teacher->initials())->toBe('BS')
        ->and($student->slug)->toBe('std-010-alya-putri-ramadhani')
        ->and($student->initials())->toBe('AP')
        ->and($teacher->user->is($teacherUser))->toBeTrue()
        ->and($teacher->subject->is($subject))->toBeTrue()
        ->and($teacher->schoolClasses)->toHaveCount(2)
        ->and($classA->teachers)->toHaveCount(1)
        ->and($classA->students)->toHaveCount(1)
        ->and($student->schoolClass->is($classA))->toBeTrue()
        ->and($attendance->date)->toBeInstanceOf(Carbon::class)
        ->and($attendance->student->is($student))->toBeTrue()
        ->and($attendance->schoolClass->is($classA))->toBeTrue()
        ->and($grade->score)->toBe('94.50')
        ->and($grade->student->is($student))->toBeTrue()
        ->and($grade->subject->is($subject))->toBeTrue();
});

test('archivable trait hides archived rows via global scope, restores them, and clears dashboard cache', function () {
    $class = SchoolClass::create(['code' => 'CLS-ARC', 'name' => '12 IPS 1', 'capacity' => 30]);
    $student = Student::create([
        'student_code' => 'STD-ARC',
        'name' => 'Arsip Siswa',
        'class_id' => $class->id,
        'status' => 'Aktif',
    ]);

    Cache::put('edudash_dashboard_stats', ['cached' => true], 60);

    expect($student->isArchived())->toBeFalse()
        ->and(Student::find($student->id))->not->toBeNull();

    $student->archive();

    expect($student->fresh()->isArchived())->toBeTrue()
        ->and(Cache::has('edudash_dashboard_stats'))->toBeFalse()
        ->and(Student::find($student->id))->toBeNull()
        ->and(Student::onlyArchived()->find($student->id))->not->toBeNull()
        ->and(Student::withArchived()->find($student->id))->not->toBeNull();

    $student->restoreFromArchive();

    expect($student->fresh()->isArchived())->toBeFalse()
        ->and(Student::find($student->id))->not->toBeNull()
        ->and(Student::onlyArchived()->find($student->id))->toBeNull();
});

test('activity log records causer, module log name, and extra badge properties', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    $entry = ActivityLog::record('Menambahkan kelas baru', 'classes', 'CLS-01 · 10 IPA 1', 'success');

    expect($entry)->not->toBeNull()
        ->and($entry->description)->toBe('Menambahkan kelas baru')
        ->and($entry->log_name)->toBe('classes')
        ->and($entry->causer_id)->toBe($admin->id)
        ->and($entry->getExtraProperty('target_label'))->toBe('CLS-01 · 10 IPA 1')
        ->and($entry->getExtraProperty('badge_color'))->toBe('success');
});
