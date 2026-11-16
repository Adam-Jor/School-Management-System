<?php

use App\Livewire\ClassesTable;
use App\Livewire\SubjectsTable;
use App\Livewire\UsersTable;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

test('auth supports username login, blocks inactive users, and logs out cleanly', function () {
    $active = User::factory()->create([
        'username' => 'budi.guru',
        'email' => 'budi.guru@sch.id',
        'role' => 'teacher',
        'status' => 'Aktif',
    ]);

    $this->post('/login', ['email' => 'budi.guru', 'password' => 'password'])
        ->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($active);

    $this->post('/logout')->assertRedirect('/login');
    $this->assertGuest();

    User::factory()->create([
        'username' => 'nonaktif.user',
        'email' => 'nonaktif@sch.id',
        'role' => 'student',
        'status' => 'Nonaktif',
    ]);

    $this->post('/login', ['email' => 'nonaktif@sch.id', 'password' => 'password'])
        ->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('admin can create, edit, archive users, prevent self-archive, and filter UsersTable', function () {
    $admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin Utama']);

    $this->actingAs($admin);

    $this->post('/users', [
        'name' => 'Operator Sekolah',
        'username' => 'operator.sekolah',
        'email' => 'operator@sch.id',
        'password' => 'secret123',
        'phone' => '081234567890',
        'role' => 'teacher',
        'status' => 'Aktif',
    ])->assertRedirect('/users');

    $created = User::where('email', 'operator@sch.id')->firstOrFail();
    expect(Hash::check('secret123', $created->password))->toBeTrue();

    $this->get(route('users.edit', $created))->assertStatus(200);
    $this->put(route('users.update', $created), [
        'name' => 'Operator Sekolah Updated',
        'username' => 'operator.sekolah',
        'email' => 'operator@sch.id',
        'password' => '',
        'phone' => '081299990000',
        'role' => 'admin',
        'status' => 'Aktif',
    ])->assertRedirect('/users');

    expect($created->refresh()->name)->toBe('Operator Sekolah Updated')
        ->and($created->role)->toBe('admin');

    Livewire::test(UsersTable::class)
        ->set('search', 'Operator Sekolah Updated')
        ->set('role', 'admin')
        ->assertSee('operator@sch.id')
        ->call('resetFilters')
        ->assertSet('search', '')
        ->assertSet('role', '');

    // Self-archive must not archive the currently signed-in admin
    $this->patch(route('users.archive', $admin))->assertRedirect('/users');
    expect(User::find($admin->id))->not->toBeNull();

    // Other user archive works (use refreshed slug after name change)
    $this->patch(route('users.archive', $created))->assertRedirect('/users');
    expect(User::find($created->id))->toBeNull();
});

test('admin can full-CRUD students with auto user creation, WebP photo, exports, and CSV import', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $classA = SchoolClass::create(['code' => 'CLS-10A1', 'name' => '10 IPA 1', 'capacity' => 36]);
    $classB = SchoolClass::create(['code' => 'CLS-10A2', 'name' => '10 IPA 2', 'capacity' => 36]);

    $this->actingAs($admin);

    $photo = UploadedFile::fake()->image('siswa.jpg', 120, 120);
    $this->post('/students', [
        'student_code' => 'STD-501',
        'name' => 'Zahra Amelia',
        'class_id' => $classA->id,
        'status' => 'Aktif',
        'phone' => '081234567801',
        'address' => 'Jl. Merdeka No. 10',
        'photo' => $photo,
    ])->assertRedirect('/students');

    $student = Student::where('student_code', 'STD-501')->firstOrFail();

    try {
        expect($student->user)->not->toBeNull()
            ->and($student->user->role)->toBe('student')
            ->and($student->img_url)->toStartWith('uploads/students/')
            ->and(file_exists(public_path($student->img_url)))->toBeTrue();

        $this->get(route('students.edit', $student))->assertStatus(200);
        $this->put(route('students.update', $student), [
            'student_code' => 'STD-501',
            'name' => 'Zahra Amelia Putri',
            'class_id' => $classB->id,
            'user_id' => $student->user_id,
            'status' => 'Menunggu',
            'phone' => '081234567801',
        ])->assertRedirect('/students');

        expect($student->fresh()->name)->toBe('Zahra Amelia Putri')
            ->and($student->fresh()->class_id)->toBe($classB->id);

        // CSV & PDF exports
        $csvRes = $this->get('/students?export=csv')->assertStatus(200);
        expect($csvRes->headers->get('content-disposition'))->toContain('.csv');

        $pdfRes = $this->get('/students?export=pdf')->assertStatus(200);
        expect($pdfRes->headers->get('content-type'))->toContain('application/pdf');

        // CSV import
        $csvContent = "student_code,name,status\nSTD-502,Fajar Sidik,Aktif\n";
        $csvFile = UploadedFile::fake()->createWithContent('students.csv', $csvContent);
        $this->post('/students/import', ['excel_file' => $csvFile])->assertRedirect('/students');
        expect(Student::where('student_code', 'STD-502')->exists())->toBeTrue();
    } finally {
        if ($student->img_url && file_exists(public_path($student->img_url))) {
            @unlink(public_path($student->img_url));
        }
    }
});

test('admin can full-CRUD teachers with class assignment sync, exports, and CSV import', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $subject = Subject::create(['code' => 'MP-301', 'name' => 'Sejarah', 'credit_hours' => 3]);
    $classA = SchoolClass::create(['code' => 'CLS-1', 'name' => '10 IPS 1', 'capacity' => 32]);
    $classB = SchoolClass::create(['code' => 'CLS-2', 'name' => '10 IPS 2', 'capacity' => 32]);

    $this->actingAs($admin);

    $this->post('/teachers', [
        'teacher_code' => 'TCH-501',
        'name' => 'Rizki Kurniawan, M.Pd',
        'subject_id' => $subject->id,
        'class_ids' => [$classA->id],
        'phone' => '081234567890',
        'status' => 'Aktif',
    ])->assertRedirect('/teachers');

    $teacher = Teacher::where('teacher_code', 'TCH-501')->firstOrFail();
    expect($teacher->schoolClasses->pluck('id')->all())->toBe([$classA->id]);

    $this->get(route('teachers.edit', $teacher))->assertStatus(200);
    $this->put(route('teachers.update', $teacher), [
        'teacher_code' => 'TCH-501',
        'name' => 'Rizki Kurniawan, M.Pd',
        'subject_id' => $subject->id,
        'user_id' => $teacher->user_id,
        'class_ids' => [$classB->id],
        'phone' => '081234567891',
        'status' => 'Cuti',
    ])->assertRedirect('/teachers');

    expect($teacher->fresh()->status)->toBe('Cuti')
        ->and($teacher->fresh()->schoolClasses->pluck('id')->all())->toBe([$classB->id]);

    $xlsxRes = $this->get('/teachers?export=xlsx')->assertStatus(200);
    expect($xlsxRes->headers->get('content-disposition'))->toContain('.xlsx');

    $pdfRes = $this->get('/teachers?export=pdf')->assertStatus(200);
    expect($pdfRes->headers->get('content-type'))->toContain('application/pdf');

    $csvFile = UploadedFile::fake()->createWithContent('teachers.csv', "teacher_code,name,phone\nTCH-502,Maya Sari,081355556666\n");
    $this->post('/teachers/import', ['excel_file' => $csvFile])->assertRedirect('/teachers');
    expect(Teacher::where('teacher_code', 'TCH-502')->exists())->toBeTrue();

    $this->patch(route('teachers.archive', $teacher))->assertRedirect('/teachers');
    expect(Teacher::find($teacher->id))->toBeNull();
});

test('admin can full-CRUD classes and subjects and filter their Livewire tables', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    // Classes CRUD
    $this->post('/classes', [
        'code' => 'CLS-12B',
        'name' => '12 Bahasa',
        'capacity' => 28,
        'description' => 'Kelas Peminatan Bahasa',
    ])->assertRedirect('/classes');

    $class = SchoolClass::where('code', 'CLS-12B')->firstOrFail();
    $this->get(route('classes.edit', $class))->assertStatus(200);
    $this->put(route('classes.update', $class), [
        'code' => 'CLS-12B',
        'name' => '12 Bahasa Unggulan',
        'capacity' => 30,
    ])->assertRedirect('/classes');

    Livewire::test(ClassesTable::class)
        ->set('search', 'Bahasa Unggulan')
        ->call('sortBy', 'capacity')
        ->assertSee('CLS-12B');

    // Subjects CRUD
    $this->post('/subjects', [
        'code' => 'MP-901',
        'name' => 'Sosiologi',
        'credit_hours' => 4,
        'description' => 'Ilmu kemasyarakatan',
    ])->assertRedirect('/subjects');

    $subject = Subject::where('code', 'MP-901')->firstOrFail();
    $this->get(route('subjects.edit', $subject))->assertStatus(200);
    $this->put(route('subjects.update', $subject), [
        'code' => 'MP-901',
        'name' => 'Sosiologi Terapan',
        'credit_hours' => 5,
    ])->assertRedirect('/subjects');

    Livewire::test(SubjectsTable::class)
        ->set('search', 'Sosiologi')
        ->call('sortBy', 'credit_hours')
        ->assertSee('MP-901');

    $class->refresh();
    $subject->refresh();

    $this->patch(route('classes.archive', $class))->assertRedirect('/classes');
    $this->patch(route('subjects.archive', $subject))->assertRedirect('/subjects');

    expect(SchoolClass::onlyArchived()->find($class->id))->not->toBeNull()
        ->and(Subject::onlyArchived()->find($subject->id))->not->toBeNull();
});

test('archive module lists and restores all five entity types and returns 404 on invalid type', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'student', 'archived' => 1]);
    $class = SchoolClass::create(['code' => 'CLS-AR', 'name' => '10 AR', 'capacity' => 30, 'archived' => 1]);
    $subject = Subject::create(['code' => 'MP-AR', 'name' => 'Seni', 'credit_hours' => 2, 'archived' => 1]);
    $teacher = Teacher::create(['teacher_code' => 'TCH-AR', 'name' => 'Guru AR', 'subject_id' => $subject->id, 'phone' => '081200001111', 'status' => 'Aktif', 'archived' => 1]);
    $student = Student::create(['student_code' => 'STD-AR', 'name' => 'Siswa AR', 'class_id' => $class->id, 'status' => 'Aktif', 'archived' => 1]);

    $this->actingAs($admin);

    foreach (['students', 'teachers', 'classes', 'subjects', 'users'] as $tab) {
        $this->get('/archive?tab='.$tab)->assertStatus(200);
    }

    $this->patch(route('archive.restore', ['type' => 'classes', 'id' => $class->id]))->assertRedirect('/archive?tab=classes');
    $this->patch(route('archive.restore', ['type' => 'subjects', 'id' => $subject->id]))->assertRedirect('/archive?tab=subjects');
    $this->patch(route('archive.restore', ['type' => 'teachers', 'id' => $teacher->id]))->assertRedirect('/archive?tab=teachers');
    $this->patch(route('archive.restore', ['type' => 'students', 'id' => $student->id]))->assertRedirect('/archive?tab=students');
    $this->patch(route('archive.restore', ['type' => 'users', 'id' => $user->id]))->assertRedirect('/archive?tab=users');
    $this->patch(route('archive.restore', ['type' => 'unknown', 'id' => 1]))->assertStatus(404);

    expect(SchoolClass::find($class->id))->not->toBeNull()
        ->and(Subject::find($subject->id))->not->toBeNull()
        ->and(Teacher::find($teacher->id))->not->toBeNull()
        ->and(Student::find($student->id))->not->toBeNull()
        ->and(User::find($user->id))->not->toBeNull();
});

test('grades calculate A/B/C/D predicates, support filters, and generate student report PDF', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $class = SchoolClass::create(['code' => 'CLS-GR', 'name' => '11 IPA 1', 'capacity' => 36]);
    $student = Student::create(['student_code' => 'STD-GR', 'name' => 'Siswa Nilai', 'class_id' => $class->id, 'status' => 'Aktif']);

    $subjects = [
        ['code' => 'MP-A', 'name' => 'Mapel A', 'score' => 92, 'predicate' => 'A'],
        ['code' => 'MP-B', 'name' => 'Mapel B', 'score' => 83, 'predicate' => 'B'],
        ['code' => 'MP-C', 'name' => 'Mapel C', 'score' => 74, 'predicate' => 'C'],
        ['code' => 'MP-D', 'name' => 'Mapel D', 'score' => 60, 'predicate' => 'D'],
    ];

    $this->actingAs($admin);

    foreach ($subjects as $item) {
        $sub = Subject::create(['code' => $item['code'], 'name' => $item['name'], 'credit_hours' => 3]);
        $this->post('/grades', [
            'student_id' => $student->id,
            'subject_id' => $sub->id,
            'semester' => 'Ganjil 2026/2027',
            'score' => $item['score'],
        ])->assertRedirect('/grades');

        expect(Grade::where('student_id', $student->id)->where('subject_id', $sub->id)->first()?->predicate)
            ->toBe($item['predicate']);
    }

    $this->get('/grades?class_id='.$class->id)->assertStatus(200)->assertSee('Siswa Nilai');
    $pdfRes = $this->get(route('grades.report.pdf', $student))->assertStatus(200);
    expect($pdfRes->headers->get('content-type'))->toContain('application/pdf');
});

test('dashboard global_q search respects role boundaries', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $teacherUser = User::factory()->create(['role' => 'teacher']);
    $studentUser1 = User::factory()->create(['role' => 'student']);
    $studentUser2 = User::factory()->create(['role' => 'student']);

    $class = SchoolClass::create(['code' => 'CLS-10G', 'name' => '10 IPA Global', 'capacity' => 36]);
    $subject = Subject::create(['code' => 'MP-G', 'name' => 'Matematika Global', 'credit_hours' => 4]);
    Teacher::create(['teacher_code' => 'TCH-G', 'name' => 'Guru Global', 'subject_id' => $subject->id, 'phone' => '081234567890', 'status' => 'Aktif']);
    Student::create(['user_id' => $studentUser1->id, 'student_code' => 'STD-G1', 'name' => 'Alya Global', 'class_id' => $class->id, 'status' => 'Aktif']);
    Student::create(['user_id' => $studentUser2->id, 'student_code' => 'STD-G2', 'name' => 'Bima Global', 'class_id' => $class->id, 'status' => 'Aktif']);

    // Admin sees students, teachers, and classes matching "Global"
    $this->actingAs($admin)->get('/dashboard?global_q=Global')
        ->assertStatus(200)
        ->assertSee('Alya Global')
        ->assertSee('Bima Global')
        ->assertSee('Guru Global')
        ->assertSee('10 IPA Global');

    // Teacher sees students & classes, NOT teachers
    $this->actingAs($teacherUser)->get('/dashboard?global_q=Global')
        ->assertStatus(200)
        ->assertSee('Alya Global')
        ->assertSee('10 IPA Global')
        ->assertDontSee('Guru Global');

    // Student sees only own student record & classes, NOT other students or teachers
    $this->actingAs($studentUser1)->get('/dashboard?global_q=Global')
        ->assertStatus(200)
        ->assertSee('Alya Global')
        ->assertSee('10 IPA Global')
        ->assertDontSee('Bima Global')
        ->assertDontSee('Guru Global');
});
