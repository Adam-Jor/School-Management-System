<?php

use App\Livewire\StudentsTable;
use App\Livewire\TeachersTable;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;

test('login form validation and authentication work cleanly', function () {
    $this->get('/login')->assertStatus(200);

    $this->post('/login', ['email' => '', 'password' => ''])
        ->assertSessionHasErrors(['email', 'password']);

    $admin = User::factory()->create([
        'email' => 'admin@test.sch.id',
        'username' => 'admin.test',
        'role' => 'admin',
    ]);

    $this->post('/login', [
        'email' => 'admin@test.sch.id',
        'password' => 'password',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($admin);
    expect(Activity::where('log_name', 'auth')->exists())->toBeTrue();
});

test('role access control restricts teacher and student to read only', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $teacher = User::factory()->create(['role' => 'teacher']);
    $student = User::factory()->create(['role' => 'student']);

    $schoolClass = SchoolClass::create([
        'code' => 'CLS-10A1',
        'name' => '10 IPA 1',
        'capacity' => 36,
    ]);

    $this->actingAs($teacher)->get('/dashboard')->assertStatus(200);
    $this->actingAs($teacher)->get('/students')->assertStatus(200);
    $this->actingAs($student)->get('/dashboard')->assertStatus(200);

    $this->actingAs($teacher)->get('/students/add')->assertStatus(403);
    $this->actingAs($student)->get('/students/add')->assertStatus(403);

    $this->actingAs($admin)->get('/students/add')->assertStatus(200);
    $this->actingAs($admin)->post('/students', [
        'student_code' => 'STD-101',
        'name' => 'Rizky Ramadhan',
        'class_id' => $schoolClass->id,
        'status' => 'Aktif',
    ])->assertRedirect('/students');

    $created = Student::where('student_code', 'STD-101')->firstOrFail();

    $this->actingAs($admin)
        ->patch(route('students.archive', $created))
        ->assertRedirect('/students');

    expect(Student::find($created->id))->toBeNull()
        ->and(Student::onlyArchived()->find($created->id))->not->toBeNull();

    $this->actingAs($admin)
        ->patch(route('archive.restore', ['type' => 'students', 'id' => $created->id]))
        ->assertRedirect(route('archive.index', ['tab' => 'students']));

    expect(Student::find($created->id))->not->toBeNull();
});

test('livewire students and teachers tables support server-side search, filtering, and sorting', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $classA = SchoolClass::create(['code' => 'CLS-10A1', 'name' => '10 IPA 1', 'capacity' => 36]);
    $subjectMath = Subject::create(['code' => 'MP-001', 'name' => 'Matematika Wajib', 'credit_hours' => 4]);

    Student::create([
        'student_code' => 'STD-201',
        'name' => 'Alya Putri',
        'class_id' => $classA->id,
        'status' => 'Aktif',
    ]);
    Student::create([
        'student_code' => 'STD-202',
        'name' => 'Bagas Wijaya',
        'class_id' => $classA->id,
        'status' => 'Pindah',
    ]);

    Teacher::create([
        'teacher_code' => 'TCH-201',
        'name' => 'Budi Santoso, S.Pd',
        'subject_id' => $subjectMath->id,
        'phone' => '081234567890',
        'status' => 'Aktif',
    ]);

    $this->actingAs($admin);

    Livewire::test(StudentsTable::class)
        ->set('search', 'Alya')
        ->assertSee('STD-201')
        ->assertDontSee('STD-202')
        ->set('search', '')
        ->set('status', 'Pindah')
        ->assertSee('Bagas Wijaya')
        ->assertDontSee('Alya Putri');

    Livewire::test(TeachersTable::class)
        ->set('search', 'Budi')
        ->assertSee('TCH-201')
        ->assertSee('Matematika Wajib');
});

test('maatwebsite excel exports students as xlsx download', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $classA = SchoolClass::create(['code' => 'CLS-10A1', 'name' => '10 IPA 1', 'capacity' => 36]);
    Student::create([
        'student_code' => 'STD-301',
        'name' => 'Citra Lestari',
        'class_id' => $classA->id,
        'status' => 'Aktif',
    ]);

    $response = $this->actingAs($admin)->get('/students?export=xlsx');

    $response->assertStatus(200);
    expect($response->headers->get('content-disposition'))->toContain('.xlsx');
});

test('dashboard displays real database KPI stats and 12-month student growth counts', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $classA = SchoolClass::create(['code' => 'CLS-10A1', 'name' => '10 IPA 1', 'capacity' => 36]);

    Student::forceCreate([
        'student_code' => 'STD-401',
        'name' => 'Siswa Bulan Lalu',
        'class_id' => $classA->id,
        'status' => 'Aktif',
        'created_at' => now()->subMonths(2),
        'updated_at' => now()->subMonths(2),
    ]);
    Student::forceCreate([
        'student_code' => 'STD-402',
        'name' => 'Siswa Bulan Ini',
        'class_id' => $classA->id,
        'status' => 'Menunggu',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get('/dashboard');

    $response->assertStatus(200)
        ->assertViewHas('stats', fn ($stats) => $stats['students'] === 2 && $stats['students_active'] === 1)
        ->assertViewHas('chartData', function ($chart) {
            return count($chart['labels']) === 12
                && count($chart['values']) === 12
                && $chart['values'][8] === 0
                && $chart['values'][9] === 1
                && $chart['values'][10] === 1
                && $chart['values'][11] === 2
                && $chart['status_values'] === [1, 0, 1];
        });
});
