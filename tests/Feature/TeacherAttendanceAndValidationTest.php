<?php

use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\UploadedFile;

test('teacher can view and record attendance only for classes they currently teach', function () {
    $teacherUser = User::factory()->create(['role' => 'teacher', 'email' => 'guru.kelas@test.id']);
    $subject = Subject::create(['code' => 'MP-101', 'name' => 'Fisika Dasar', 'credit_hours' => 4]);

    $taughtClass = SchoolClass::create(['code' => 'CLS-11A1', 'name' => '11 IPA 1', 'capacity' => 36]);
    $otherClass = SchoolClass::create(['code' => 'CLS-11A2', 'name' => '11 IPA 2', 'capacity' => 36]);

    $teacher = Teacher::create([
        'user_id' => $teacherUser->id,
        'subject_id' => $subject->id,
        'teacher_code' => 'TCH-701',
        'name' => 'Guru Fisika',
        'phone' => '081234567890',
        'status' => 'Aktif',
    ]);
    $teacher->schoolClasses()->sync([$taughtClass->id]);

    $studentInTaught = Student::create([
        'student_code' => 'STD-701',
        'name' => 'Nadia Putri',
        'class_id' => $taughtClass->id,
        'status' => 'Aktif',
    ]);
    $studentInOther = Student::create([
        'student_code' => 'STD-702',
        'name' => 'Raka Pratama',
        'class_id' => $otherClass->id,
        'status' => 'Aktif',
    ]);

    $this->actingAs($teacherUser);

    // Can view taught class and see submit controls
    $this->get('/attendances?class_id='.$taughtClass->id)
        ->assertStatus(200)
        ->assertSee('Nadia Putri')
        ->assertSee('Simpan Absensi Harian')
        ->assertDontSee('Raka Pratama');

    // Can store attendance for taught class
    $today = now()->toDateString();
    $this->post('/attendances', [
        'class_id' => $taughtClass->id,
        'date' => $today,
        'statuses' => [$studentInTaught->id => 'Sakit'],
        'notes' => [$studentInTaught->id => 'Surat keterangan dokter'],
    ])->assertRedirect(route('attendances.index', [
        'class_id' => $taughtClass->id,
        'date' => $today,
    ]));

    expect(Attendance::where('student_id', $studentInTaught->id)->whereDate('date', $today)->first())
        ->not->toBeNull()
        ->status->toBe('Sakit')
        ->notes->toBe('Surat keterangan dokter');

    // Forbidden from viewing or recording attendance for a class they do not teach
    $this->get('/attendances?class_id='.$otherClass->id)->assertStatus(403);
    $this->post('/attendances', [
        'class_id' => $otherClass->id,
        'date' => $today,
        'statuses' => [$studentInOther->id => 'Hadir'],
    ])->assertStatus(403);

    // FormRequest rejects tampering a student from another class into the taught class submission
    $this->post('/attendances', [
        'class_id' => $taughtClass->id,
        'date' => $today,
        'statuses' => [$studentInOther->id => 'Hadir'],
    ])->assertSessionHasErrors('statuses');
});

test('all form inputs validate via dedicated Form Request classes', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $otherUser = User::factory()->create(['role' => 'student', 'email' => 'taken@test.id']);
    $classA = SchoolClass::create(['code' => 'CLS-12A1', 'name' => '12 IPA 1', 'capacity' => 32]);
    $subject = Subject::create(['code' => 'MP-201', 'name' => 'Kimia', 'credit_hours' => 4]);
    $student = Student::create([
        'student_code' => 'STD-801',
        'name' => 'Test Student',
        'class_id' => $classA->id,
        'status' => 'Aktif',
    ]);

    $this->actingAs($admin);

    // AttendanceRequest validation
    $this->post('/attendances', [
        'class_id' => '',
        'date' => 'not-a-date',
        'statuses' => [$student->id => 'Bolos'],
    ])->assertSessionHasErrors(['class_id', 'date', 'statuses.'.$student->id]);

    // GradeRequest validation
    $this->post('/grades', [
        'student_id' => '',
        'subject_id' => '',
        'semester' => '',
        'score' => 150,
    ])->assertSessionHasErrors(['student_id', 'subject_id', 'semester', 'score']);

    // ProfileUpdateRequest validation
    $this->put('/profile', [
        'name' => '',
        'email' => 'taken@test.id',
        'phone' => 'invalid-phone',
        'password' => '123',
    ])->assertSessionHasErrors(['name', 'email', 'phone', 'password']);

    // ExcelImportRequest validation
    $this->post('/students/import', [
        'excel_file' => UploadedFile::fake()->create('photo.png', 50, 'image/png'),
    ])->assertSessionHasErrors('excel_file');

    $this->post('/teachers/import', [])
        ->assertSessionHasErrors('excel_file');

    // Existing resource FormRequests
    $this->post('/students', [])->assertSessionHasErrors(['student_code', 'name', 'class_id', 'status']);
    $this->post('/teachers', [])->assertSessionHasErrors(['teacher_code', 'name', 'subject_id', 'phone', 'status']);
    $this->post('/classes', ['capacity' => 5])->assertSessionHasErrors(['code', 'name', 'capacity']);
    $this->post('/subjects', ['credit_hours' => 99])->assertSessionHasErrors(['code', 'name', 'credit_hours']);
    $this->post('/users', [])->assertSessionHasErrors(['name', 'username', 'email', 'password', 'role', 'status']);
});

test('frontend views never display internal database table names such as tbl_users', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $internalNames = ['tbl_users', 'tbl_students', 'tbl_teachers', 'tbl_classes', 'tbl_subjects', 'tbl_attendances', 'tbl_grades'];

    $loginHtml = $this->get('/login')->assertStatus(200)->getContent();
    foreach ($internalNames as $tableName) {
        expect($loginHtml)->not->toContain($tableName);
    }

    $this->actingAs($admin);
    foreach (['/students/add', '/teachers/add', '/classes/add', '/subjects/add', '/users', '/users/add', '/attendances', '/grades', '/archive'] as $uri) {
        $html = $this->get($uri)->assertStatus(200)->getContent();
        foreach ($internalNames as $tableName) {
            expect($html)->not->toContain($tableName);
        }
    }
});
