<?php

use App\Livewire\StudentsTable;
use App\Models\AcademicPeriod;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->adminUser = User::forceCreate([
        'name' => 'Admin User',
        'email' => 'admin@test.com',
        'username' => 'admin_test',
        'password' => bcrypt('password'),
        'role' => 'admin',
        'status' => 'Aktif',
    ]);

    $this->classA = SchoolClass::create([
        'code' => 'CLS-10A',
        'name' => '10 IPA 1',
        'capacity' => 36,
    ]);

    $this->classB = SchoolClass::create([
        'code' => 'CLS-10B',
        'name' => '10 IPA 2',
        'capacity' => 36,
    ]);

    $this->subject = Subject::create([
        'code' => 'MP-MAT',
        'name' => 'Matematika Wajib',
        'credit_hours' => 4,
    ]);

    $this->teacherUser = User::forceCreate([
        'name' => 'Teacher User',
        'email' => 'teacher@test.com',
        'username' => 'teacher_test',
        'password' => bcrypt('password'),
        'role' => 'teacher',
        'status' => 'Aktif',
    ]);

    $this->teacher = Teacher::create([
        'user_id' => $this->teacherUser->id,
        'subject_id' => $this->subject->id,
        'teacher_code' => 'TCH-001',
        'name' => 'Teacher User',
        'status' => 'Aktif',
    ]);

    // Assign teacher to classA
    $this->teacher->schoolClasses()->attach($this->classA->id);

    $this->studentUserA = User::forceCreate([
        'name' => 'Student A',
        'email' => 'student_a@test.com',
        'username' => 'student_a',
        'password' => bcrypt('password'),
        'role' => 'student',
        'status' => 'Aktif',
    ]);

    $this->studentA = Student::create([
        'user_id' => $this->studentUserA->id,
        'class_id' => $this->classA->id,
        'student_code' => 'STD-001',
        'name' => 'Student A',
        'status' => 'Aktif',
    ]);

    $this->studentUserB = User::forceCreate([
        'name' => 'Student B',
        'email' => 'student_b@test.com',
        'username' => 'student_b',
        'password' => bcrypt('password'),
        'role' => 'student',
        'status' => 'Aktif',
    ]);

    $this->studentB = Student::create([
        'user_id' => $this->studentUserB->id,
        'class_id' => $this->classB->id,
        'student_code' => 'STD-002',
        'name' => 'Student B',
        'status' => 'Aktif',
    ]);

    $this->period = AcademicPeriod::create([
        'name' => '2026/2027',
        'semester' => 'Ganjil',
        'is_active' => true,
    ]);
});

test('design system styleguide route renders successfully for authenticated user', function () {
    $response = $this->actingAs($this->adminUser)->get(route('design-system.index'));
    $response->assertOk()
        ->assertSee('Living Styleguide')
        ->assertSee('Command Palette')
        ->assertSee('Slide-Over / Bottom Sheet');
});

test('academic period switcher updates active period session and helpers return it', function () {
    $period2 = AcademicPeriod::create([
        'name' => '2026/2027',
        'semester' => 'Genap',
        'is_active' => false,
    ]);

    $response = $this->actingAs($this->adminUser)
        ->post(route('academic-periods.switch'), [
            'period_id' => $period2->id,
        ]);

    $response->assertSessionHas('active_academic_period_id', $period2->id);
    $response->assertSessionHas('toast');
});

test('teacher can input and update grades for students in classes they teach', function () {
    $response = $this->actingAs($this->teacherUser)
        ->post(route('grades.store'), [
            'student_id' => $this->studentA->id,
            'subject_id' => $this->subject->id,
            'semester' => 'Ganjil 2026/2027',
            'score' => 92,
        ]);

    $response->assertRedirect(route('grades.index'));
    $response->assertSessionHas('toast');

    $this->assertDatabaseHas('tbl_grades', [
        'student_id' => $this->studentA->id,
        'subject_id' => $this->subject->id,
        'score' => 92,
        'predicate' => 'A',
    ]);
});

test('teacher cannot input grades for students outside their assigned classes and subjects', function () {
    $otherSubject = Subject::create([
        'code' => 'MP-BIO',
        'name' => 'Biologi',
        'credit_hours' => 3,
    ]);

    $response = $this->actingAs($this->teacherUser)
        ->post(route('grades.store'), [
            'student_id' => $this->studentB->id,
            'subject_id' => $otherSubject->id,
            'semester' => 'Ganjil 2026/2027',
            'score' => 75,
        ]);

    $response->assertForbidden();
});

test('admin can manage schedules and schedule collision is prevented', function () {
    // 1. Sukses buat jadwal
    $response = $this->actingAs($this->adminUser)
        ->post(route('schedules.store'), [
            'class_id' => $this->classA->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'day_of_week' => 'Senin',
            'start_time' => '07:30',
            'end_time' => '09:00',
            'room' => 'Ruang 10-A1',
        ]);

    $response->assertRedirect(route('schedules.index'));
    $this->assertDatabaseHas('tbl_schedules', [
        'class_id' => $this->classA->id,
        'day_of_week' => 'Senin',
        'start_time' => '07:30',
    ]);

    // 2. Bentrok jadwal guru pada jam yang bertabrakan ditolak
    $conflictResponse = $this->actingAs($this->adminUser)
        ->post(route('schedules.store'), [
            'class_id' => $this->classB->id, // kelas beda
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id, // guru sama
            'day_of_week' => 'Senin',
            'start_time' => '08:00', // overlap!
            'end_time' => '09:30',
        ]);

    $conflictResponse->assertSessionHasErrors(['teacher_id']);

    // 3. Admin dapat mengarsipkan jadwal
    $schedule = Schedule::first();
    $archiveResponse = $this->actingAs($this->adminUser)
        ->patch(route('schedules.archive', $schedule));

    $archiveResponse->assertRedirect(route('schedules.index'));
    $this->assertDatabaseHas('tbl_schedules', [
        'id' => $schedule->id,
        'archived' => 1,
    ]);
});

test('classes show route enforces role permissions: student can only view own class', function () {
    // Student A boleh buka classA miliknya
    $responseA = $this->actingAs($this->studentUserA)
        ->get(route('classes.show', $this->classA));
    $responseA->assertOk()
        ->assertSee($this->classA->name);

    // Student A dilarang buka classB (403)
    $responseB = $this->actingAs($this->studentUserA)
        ->get(route('classes.show', $this->classB));
    $responseB->assertForbidden();

    // Admin bebas buka kelas mana saja
    $responseAdmin = $this->actingAs($this->adminUser)
        ->get(route('classes.show', $this->classB));
    $responseAdmin->assertOk();
});

test('livewire students table bulk actions can promote, update status, and archive selected students', function () {
    Livewire::actingAs($this->adminUser)
        ->test(StudentsTable::class)
        ->set('selected', [(string) $this->studentA->id, (string) $this->studentB->id])
        // 1. Bulk promote ke classB
        ->set('bulkTargetClassId', (string) $this->classB->id)
        ->call('bulkPromote')
        ->assertDispatched('notify');

    expect($this->studentA->fresh()->class_id)->toBe($this->classB->id);

    // 2. Bulk status update ke 'Pindah'
    Livewire::actingAs($this->adminUser)
        ->test(StudentsTable::class)
        ->set('selected', [(string) $this->studentA->id])
        ->set('bulkNewStatus', 'Pindah')
        ->call('bulkUpdateStatus')
        ->assertDispatched('notify');

    expect($this->studentA->fresh()->status)->toBe('Pindah');

    // 3. Bulk archive
    Livewire::actingAs($this->adminUser)
        ->test(StudentsTable::class)
        ->set('selected', [(string) $this->studentA->id])
        ->call('bulkArchive')
        ->assertDispatched('notify');

    expect($this->studentA->fresh()->archived)->toBe(1);
});
