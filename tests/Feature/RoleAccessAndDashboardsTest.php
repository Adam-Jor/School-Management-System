<?php

use App\Livewire\StudentsTable;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Livewire\Livewire;

function seedRoleTestData(): array
{
    $admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@role.test']);
    $teacherUser = User::factory()->create(['role' => 'teacher', 'email' => 'teacher@role.test', 'name' => 'Guru Matematika']);
    $studentUser = User::factory()->create(['role' => 'student', 'email' => 'student1@role.test', 'name' => 'Siswa Pertama']);
    $otherStudentUser = User::factory()->create(['role' => 'student', 'email' => 'student2@role.test', 'name' => 'Siswa Kedua']);

    $classA = SchoolClass::create(['code' => 'CLS-10A1', 'name' => '10 IPA 1', 'capacity' => 36]);
    $classB = SchoolClass::create(['code' => 'CLS-10A2', 'name' => '10 IPA 2', 'capacity' => 36]);

    $subjectMath = Subject::create(['code' => 'MP-001', 'name' => 'Matematika Wajib', 'credit_hours' => 4]);

    $teacher = Teacher::create([
        'user_id' => $teacherUser->id,
        'subject_id' => $subjectMath->id,
        'teacher_code' => 'TCH-901',
        'name' => 'Guru Matematika',
        'phone' => '081234567890',
        'status' => 'Aktif',
    ]);
    $teacher->schoolClasses()->sync([$classA->id]);

    $student1 = Student::create([
        'user_id' => $studentUser->id,
        'class_id' => $classA->id,
        'student_code' => 'STD-901',
        'name' => 'Siswa Pertama',
        'status' => 'Aktif',
    ]);

    $student2 = Student::create([
        'user_id' => $otherStudentUser->id,
        'class_id' => $classB->id,
        'student_code' => 'STD-902',
        'name' => 'Siswa Kedua',
        'status' => 'Aktif',
    ]);

    Grade::create([
        'student_id' => $student1->id,
        'subject_id' => $subjectMath->id,
        'semester' => 'Ganjil 2026/2027',
        'score' => 92,
        'predicate' => 'A',
    ]);
    Grade::create([
        'student_id' => $student2->id,
        'subject_id' => $subjectMath->id,
        'semester' => 'Ganjil 2026/2027',
        'score' => 84,
        'predicate' => 'B',
    ]);

    Attendance::create([
        'student_id' => $student1->id,
        'class_id' => $classA->id,
        'date' => now()->toDateString(),
        'status' => 'Hadir',
        'notes' => 'Catatan Hadir Pribadi',
    ]);
    Attendance::create([
        'student_id' => $student2->id,
        'class_id' => $classB->id,
        'date' => now()->toDateString(),
        'status' => 'Izin',
        'notes' => 'Izin Keluarga Rahasia',
    ]);

    return compact('admin', 'teacherUser', 'studentUser', 'otherStudentUser', 'classA', 'classB', 'subjectMath', 'teacher', 'student1', 'student2');
}

test('admin, teacher, and student each receive separate dedicated dashboards and cross-role dashboards return 403', function () {
    $data = seedRoleTestData();

    $this->actingAs($data['admin'])
        ->get('/dashboard')
        ->assertStatus(200)
        ->assertViewIs('dashboard.admin')
        ->assertSee('Ringkasan Akademik');
    $this->actingAs($data['admin'])->get('/admin/dashboard')->assertStatus(200);
    $this->actingAs($data['admin'])->get('/teacher/dashboard')->assertStatus(403);
    $this->actingAs($data['admin'])->get('/student/dashboard')->assertStatus(403);

    $this->actingAs($data['teacherUser'])
        ->get('/dashboard')
        ->assertStatus(200)
        ->assertViewIs('dashboard.teacher')
        ->assertSee('Ruang Mengajar')
        ->assertSee('Navigasi bawah mobile')
        ->assertSee('10 IPA 1')
        ->assertDontSee('10 IPA 2');
    $this->actingAs($data['teacherUser'])->get('/teacher/dashboard')->assertStatus(200);
    $this->actingAs($data['teacherUser'])->get('/admin/dashboard')->assertStatus(403);
    $this->actingAs($data['teacherUser'])->get('/student/dashboard')->assertStatus(403);

    $this->actingAs($data['studentUser'])
        ->get('/dashboard')
        ->assertStatus(200)
        ->assertViewIs('dashboard.student')
        ->assertSee('Portal Akademik Siswa')
        ->assertSee('Navigasi bawah mobile')
        ->assertSee('STD-901')
        ->assertDontSee('STD-902');
    $this->actingAs($data['studentUser'])->get('/student/dashboard')->assertStatus(200);
    $this->actingAs($data['studentUser'])->get('/admin/dashboard')->assertStatus(403);
    $this->actingAs($data['studentUser'])->get('/teacher/dashboard')->assertStatus(403);
});

test('access control matrix enforces exact admin CRUD permissions across all modules', function () {
    $data = seedRoleTestData();

    $this->actingAs($data['admin']);

    $this->get('/users')->assertStatus(200);
    $this->get('/users/add')->assertStatus(200);
    $this->get('/students')->assertStatus(200);
    $this->get('/students/add')->assertStatus(200);
    $this->get('/teachers')->assertStatus(200);
    $this->get('/teachers/add')->assertStatus(200);
    $this->get('/classes')->assertStatus(200);
    $this->get('/classes/add')->assertStatus(200);
    $this->get('/subjects')->assertStatus(200);
    $this->get('/subjects/add')->assertStatus(200);
    $this->get('/archive')->assertStatus(200);
    $this->get('/profile')->assertStatus(200);

    $this->post('/teachers', [
        'teacher_code' => 'TCH-999',
        'name' => 'Dr. Sari Dewi, M.Pd',
        'subject_id' => $data['subjectMath']->id,
        'class_ids' => [$data['classA']->id, $data['classB']->id],
        'phone' => '081234567899',
        'status' => 'Aktif',
    ])->assertRedirect('/teachers');

    $newTeacher = Teacher::where('teacher_code', 'TCH-999')->firstOrFail();
    expect($newTeacher->schoolClasses()->pluck('tbl_classes.id')->all())
        ->toEqualCanonicalizing([$data['classA']->id, $data['classB']->id]);

    $this->post('/grades', [
        'student_id' => $data['student1']->id,
        'subject_id' => $data['subjectMath']->id,
        'semester' => 'Genap 2026/2027',
        'score' => 95,
    ])->assertRedirect('/grades');

    expect(Grade::where('student_id', $data['student1']->id)->where('semester', 'Genap 2026/2027')->first()?->predicate)
        ->toBe('A');
});

test('access control matrix enforces teacher permissions: view students/classes/subjects, deny users/teachers/archive and writes, allow profile edit', function () {
    $data = seedRoleTestData();
    $this->actingAs($data['teacherUser']);

    // Allowed views
    $this->get('/dashboard')
        ->assertStatus(200)
        ->assertDontSee(route('users.index'))
        ->assertDontSee(route('teachers.index'))
        ->assertDontSee(route('archive.index'))
        ->assertSee(route('subjects.index'));
    $this->get('/students')->assertStatus(200);
    $this->get('/classes')->assertStatus(200);
    $this->get('/subjects')->assertStatus(200);
    $this->get('/profile')->assertStatus(200);

    // Denied modules per Matrix Hak Akses (-)
    $this->get('/users')->assertStatus(403);
    $this->get('/users/add')->assertStatus(403);
    $this->get('/teachers')->assertStatus(403);
    $this->get('/teachers/add')->assertStatus(403);
    $this->get('/archive')->assertStatus(403);

    // Denied writes on View-only modules
    $this->get('/students/add')->assertStatus(403);
    $this->post('/students', [])->assertStatus(403);
    $this->get(route('students.edit', $data['student1']))->assertStatus(403);
    $this->patch(route('students.archive', $data['student1']))->assertStatus(403);
    $this->get('/classes/add')->assertStatus(403);
    $this->post('/classes', [])->assertStatus(403);
    $this->get('/subjects/add')->assertStatus(403);
    $this->post('/subjects', [])->assertStatus(403);
    $this->post('/grades', [])->assertStatus(403);

    // Allowed profile update
    $this->put('/profile', [
        'name' => 'Guru Matematika Baru',
        'email' => 'teacher@role.test',
        'phone' => '081200001111',
    ])->assertRedirect('/profile');

    expect($data['teacherUser']->fresh()->name)->toBe('Guru Matematika Baru');
});

test('access control matrix enforces student permissions: deny users/teachers/subjects/archive, view own student data and classes only, allow profile edit', function () {
    $data = seedRoleTestData();
    $this->actingAs($data['studentUser']);

    // Allowed views
    $this->get('/dashboard')
        ->assertStatus(200)
        ->assertDontSee(route('users.index'))
        ->assertDontSee(route('teachers.index'))
        ->assertDontSee(route('subjects.index'))
        ->assertDontSee(route('archive.index'));
    $this->get('/classes')->assertStatus(200);
    $this->get('/students')->assertStatus(200);
    $this->get('/profile')->assertStatus(200);

    // Student sees only own data in Livewire StudentsTable & export
    Livewire::test(StudentsTable::class)
        ->assertSee('STD-901')
        ->assertSee('Siswa Pertama')
        ->assertDontSee('STD-902')
        ->assertDontSee('Siswa Kedua');

    $this->get('/students?export=xlsx')->assertStatus(200);

    // Student sees only own grades & PDF report; denied other student's PDF report
    $this->get('/grades')
        ->assertStatus(200)
        ->assertSee('STD-901')
        ->assertDontSee('STD-902');
    $this->get(route('grades.report.pdf', $data['student1']))->assertStatus(200);
    $this->get(route('grades.report.pdf', $data['student2']))->assertStatus(403);

    // Student sees only own attendance; cannot submit attendance
    $this->get('/attendances')
        ->assertStatus(200)
        ->assertSee('STD-901')
        ->assertDontSee('STD-902')
        ->assertDontSee('Izin Keluarga Rahasia');
    $this->post('/attendances', [
        'class_id' => $data['classA']->id,
        'date' => now()->toDateString(),
        'statuses' => [$data['student1']->id => 'Hadir'],
    ])->assertStatus(403);

    // Denied modules per Matrix Hak Akses (-)
    $this->get('/users')->assertStatus(403);
    $this->get('/teachers')->assertStatus(403);
    $this->get('/subjects')->assertStatus(403);
    $this->get('/archive')->assertStatus(403);

    // Denied writes
    $this->get('/students/add')->assertStatus(403);
    $this->post('/students', [])->assertStatus(403);
    $this->get('/classes/add')->assertStatus(403);
    $this->post('/classes', [])->assertStatus(403);
    $this->post('/grades', [])->assertStatus(403);

    // Allowed profile edit
    $this->put('/profile', [
        'name' => 'Siswa Pertama Update',
        'email' => 'student1@role.test',
        'phone' => '081300002222',
    ])->assertRedirect('/profile');

    expect($data['studentUser']->fresh()->name)->toBe('Siswa Pertama Update');
});
