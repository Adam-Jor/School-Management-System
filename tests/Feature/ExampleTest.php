<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_form_validation_and_authentication_work(): void
    {
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
    }

    public function test_role_access_control_restricts_teacher_and_student_to_read_only(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);

        $schoolClass = SchoolClass::create([
            'code' => 'CLS-10A1',
            'name' => '10 IPA 1',
            'capacity' => 36,
        ]);

        // Teacher & Student can read dashboard and index pages
        $this->actingAs($teacher)->get('/dashboard')->assertStatus(200);
        $this->actingAs($teacher)->get('/students')->assertStatus(200);
        $this->actingAs($student)->get('/dashboard')->assertStatus(200);

        // Teacher & Student cannot access create/store routes (403 Forbidden)
        $this->actingAs($teacher)->get('/students/add')->assertStatus(403);
        $this->actingAs($student)->get('/students/add')->assertStatus(403);

        // Admin has full CRUD access
        $this->actingAs($admin)->get('/students/add')->assertStatus(200);
        $this->actingAs($admin)->post('/students', [
            'student_code' => 'STD-101',
            'name' => 'Rizky Ramadhan',
            'class_id' => $schoolClass->id,
            'status' => 'Aktif',
        ])->assertRedirect('/students');

        $created = Student::where('student_code', 'STD-101')->firstOrFail();

        // Soft delete (archive) and restore
        $this->actingAs($admin)
            ->patch(route('students.archive', $created))
            ->assertRedirect('/students');

        $this->assertNull(Student::find($created->id));
        $this->assertNotNull(Student::onlyArchived()->find($created->id));

        $this->actingAs($admin)
            ->patch(route('archive.restore', ['type' => 'students', 'id' => $created->id]))
            ->assertRedirect(route('archive.index', ['tab' => 'students']));

        $this->assertNotNull(Student::find($created->id));
    }

    public function test_helpers_format_indonesian_numbers_and_dates(): void
    {
        $this->assertSame('10.000', format_number(10000));
        $this->assertSame('1.042', format_number(1042));
        $this->assertSame('1 Feb 2024', format_date('2024-02-01'));
    }
}
