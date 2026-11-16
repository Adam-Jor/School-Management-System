<?php

use App\Exports\StudentsExport;
use App\Exports\TeachersExport;
use App\Imports\StudentsImport;
use App\Imports\TeachersImport;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('students export filters by search, class, status, and user scoping while mapping formatted rows', function () {
    $classA = SchoolClass::create(['code' => 'CLS-10A1', 'name' => '10 IPA 1', 'capacity' => 36]);
    $classB = SchoolClass::create(['code' => 'CLS-10S1', 'name' => '10 IPS 1', 'capacity' => 34]);

    $u1 = User::factory()->create(['role' => 'student', 'email' => 'alya@sch.id']);
    $u2 = User::factory()->create(['role' => 'student', 'email' => 'bagas@sch.id']);

    $s1 = Student::create(['user_id' => $u1->id, 'class_id' => $classA->id, 'student_code' => 'STD-001', 'name' => 'Alya Putri', 'phone' => '081234000001', 'status' => 'Aktif']);
    Student::create(['user_id' => $u2->id, 'class_id' => $classB->id, 'student_code' => 'STD-002', 'name' => 'Bagas Pratama', 'status' => 'Pindah']);

    $filtered = (new StudentsExport(search: 'Alya', classId: (string) $classA->id, status: 'Aktif'))->query()->get();
    $scopedOnly = (new StudentsExport(onlyUserId: $u2->id))->query()->get();

    expect($filtered)->toHaveCount(1)
        ->and($filtered->first()->student_code)->toBe('STD-001')
        ->and($scopedOnly)->toHaveCount(1)
        ->and($scopedOnly->first()->student_code)->toBe('STD-002');

    $export = new StudentsExport;
    $mapped = $export->map($s1->load(['schoolClass', 'user']));

    expect($export->headings())->toBe(['Kode Siswa', 'Nama Lengkap', 'Kelas', 'Email Akun', 'No. Telepon', 'Status', 'Tanggal Terdaftar'])
        ->and($mapped[0])->toBe('STD-001')
        ->and($mapped[2])->toBe('10 IPA 1')
        ->and($mapped[3])->toBe('alya@sch.id')
        ->and($mapped[5])->toBe('Aktif');
});

test('teachers export filters by search, subject, status and maps columns accurately', function () {
    $math = Subject::create(['code' => 'MP-1', 'name' => 'Matematika', 'credit_hours' => 4]);
    $bio = Subject::create(['code' => 'MP-2', 'name' => 'Biologi', 'credit_hours' => 4]);

    $u1 = User::factory()->create(['role' => 'teacher', 'email' => 'budi@sch.id']);
    $t1 = Teacher::create(['user_id' => $u1->id, 'subject_id' => $math->id, 'teacher_code' => 'TCH-001', 'name' => 'Budi Santoso', 'phone' => '081234567890', 'status' => 'Aktif']);
    Teacher::create(['subject_id' => $bio->id, 'teacher_code' => 'TCH-002', 'name' => 'Rina Sari', 'phone' => '081398765432', 'status' => 'Cuti']);

    $filtered = (new TeachersExport(search: 'Matematika', subjectId: (string) $math->id, status: 'Aktif'))->query()->get();
    $export = new TeachersExport;
    $mapped = $export->map($t1->load(['subject', 'user']));

    expect($filtered)->toHaveCount(1)
        ->and($filtered->first()->teacher_code)->toBe('TCH-001')
        ->and($export->headings())->toBe(['Kode Guru', 'Nama Guru', 'Mata Pelajaran', 'Email Akun', 'No. Telepon', 'Status', 'Tanggal Terdaftar'])
        ->and($mapped[0])->toBe('TCH-001')
        ->and($mapped[2])->toBe('Matematika')
        ->and($mapped[3])->toBe('budi@sch.id');
});

test('students import skips header, blank, and duplicate codes while assigning default class and valid status', function () {
    $defaultClass = SchoolClass::create(['code' => 'CLS-DEF', 'name' => '10 IPA 1', 'capacity' => 36]);
    Student::create(['student_code' => 'STD-DUP', 'name' => 'Existing Archived', 'class_id' => $defaultClass->id, 'status' => 'Aktif', 'archived' => 1]);

    $importer = new StudentsImport;
    $importer->collection(collect([
        ['Kode Siswa', 'Nama', 'Status'],
        ['STD-NEW1', 'Dewi Anggraini', 'Menunggu'],
        ['STD-NEW2', 'Eko Saputra', 'StatusTidakValid'],
        ['STD-DUP', 'Duplikat Kode', 'Aktif'],
        ['', 'Kosong Kode', 'Aktif'],
    ]));

    expect($importer->imported)->toBe(2)
        ->and(Student::where('student_code', 'STD-NEW1')->first()?->status)->toBe('Menunggu')
        ->and(Student::where('student_code', 'STD-NEW1')->first()?->class_id)->toBe($defaultClass->id)
        ->and(Student::where('student_code', 'STD-NEW2')->first()?->status)->toBe('Aktif');
});

test('teachers import skips header and duplicates while creating active teacher records with default subject', function () {
    $defaultSubject = Subject::create(['code' => 'MP-DEF', 'name' => 'Informatika', 'credit_hours' => 3]);
    Teacher::create(['teacher_code' => 'TCH-DUP', 'name' => 'Guru Duplikat', 'subject_id' => $defaultSubject->id, 'phone' => '081111111111', 'status' => 'Aktif']);

    $importer = new TeachersImport;
    $importer->collection(collect([
        ['Code', 'Name', 'Phone'],
        ['TCH-NEW1', 'Hendra Wijaya, S.Kom', '085712345678'],
        ['TCH-DUP', 'Duplikat', '081200000000'],
        ['TCH-EMPTY', '', '081200000000'],
    ]));

    $created = Teacher::where('teacher_code', 'TCH-NEW1')->first();

    expect($importer->imported)->toBe(1)
        ->and($created)->not->toBeNull()
        ->and($created->subject_id)->toBe($defaultSubject->id)
        ->and($created->status)->toBe('Aktif');
});
