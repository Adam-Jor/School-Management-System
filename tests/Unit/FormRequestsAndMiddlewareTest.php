<?php

use App\Http\Middleware\RoleMiddleware;
use App\Http\Requests\AttendanceRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\ClassRequest;
use App\Http\Requests\ExcelImportRequest;
use App\Http\Requests\GradeRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\StudentRequest;
use App\Http\Requests\SubjectRequest;
use App\Http\Requests\TeacherRequest;
use App\Http\Requests\UserRequest;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('role middleware redirects guests, blocks unauthorized roles with 403, and allows permitted roles', function () {
    $middleware = new RoleMiddleware;
    $next = fn (Request $req) => new Response('OK', 200);

    // Unauthenticated
    $guestReq = Request::create('/students', 'GET');
    $guestRes = $middleware->handle($guestReq, $next, 'admin');
    expect($guestRes)->toBeInstanceOf(RedirectResponse::class)
        ->and($guestRes->getTargetUrl())->toEndWith('/login');

    // Permitted role (multi-role)
    $teacher = User::factory()->create(['role' => 'teacher']);
    $teacherReq = Request::create('/subjects', 'GET');
    $teacherReq->setUserResolver(fn () => $teacher);
    $okRes = $middleware->handle($teacherReq, $next, 'admin', 'teacher');
    expect($okRes->getStatusCode())->toBe(200);

    // Unauthorized role
    $student = User::factory()->create(['role' => 'student']);
    $studentReq = Request::create('/subjects', 'GET');
    $studentReq->setUserResolver(fn () => $student);
    expect(fn () => $middleware->handle($studentReq, $next, 'admin', 'teacher'))
        ->toThrow(HttpException::class);
});

test('login request validates credentials, computes throttle key, and enforces lockout after 5 attempts', function () {
    $loginReq = LoginRequest::create('/login', 'POST', [
        'email' => 'Admin@EduDash.Sch.ID',
        'password' => '123',
    ]);
    $loginReq->server->set('REMOTE_ADDR', '127.0.0.1');

    expect($loginReq->authorize())->toBeTrue()
        ->and($loginReq->throttleKey())->toBe('admin@edudash.sch.id|127.0.0.1');

    $validator = Validator::make($loginReq->all(), $loginReq->rules(), $loginReq->messages());
    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('password'))->toBeTrue();

    RateLimiter::clear($loginReq->throttleKey());
    for ($i = 0; $i < 5; $i++) {
        RateLimiter::hit($loginReq->throttleKey());
    }

    expect(fn () => $loginReq->ensureIsNotRateLimited())
        ->toThrow(ValidationException::class);

    RateLimiter::clear($loginReq->throttleKey());
});

test('attendance request authorizes only admin or teacher assigned to class and rejects foreign student IDs', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $teacherUser = User::factory()->create(['role' => 'teacher']);
    $studentUser = User::factory()->create(['role' => 'student']);

    $subject = Subject::create(['code' => 'MP-1', 'name' => 'Biologi', 'credit_hours' => 4]);
    $taughtClass = SchoolClass::create(['code' => 'CLS-A', 'name' => '10 IPA 1', 'capacity' => 36]);
    $otherClass = SchoolClass::create(['code' => 'CLS-B', 'name' => '10 IPA 2', 'capacity' => 36]);

    $teacher = Teacher::create([
        'user_id' => $teacherUser->id,
        'subject_id' => $subject->id,
        'teacher_code' => 'TCH-01',
        'name' => 'Guru Biologi',
        'phone' => '081234567890',
        'status' => 'Aktif',
    ]);
    $teacher->schoolClasses()->sync([$taughtClass->id]);

    $studentInTaught = Student::create(['student_code' => 'STD-1', 'name' => 'Siswa A', 'class_id' => $taughtClass->id, 'status' => 'Aktif']);
    $studentInOther = Student::create(['student_code' => 'STD-2', 'name' => 'Siswa B', 'class_id' => $otherClass->id, 'status' => 'Aktif']);

    $makeReq = function (?User $actor, int $classId, array $statuses = []) {
        $req = AttendanceRequest::create('/attendances', 'POST', [
            'class_id' => $classId,
            'date' => '2026-11-16',
            'statuses' => $statuses,
        ]);
        $req->setUserResolver(fn () => $actor);

        return $req;
    };

    expect($makeReq($admin, $otherClass->id)->authorize())->toBeTrue()
        ->and($makeReq($teacherUser, $taughtClass->id)->authorize())->toBeTrue()
        ->and($makeReq($teacherUser, $otherClass->id)->authorize())->toBeFalse()
        ->and($makeReq($studentUser, $taughtClass->id)->authorize())->toBeFalse()
        ->and($makeReq(null, $taughtClass->id)->authorize())->toBeFalse();

    // Custom after-validator rejects student belonging to a different class
    $invalidReq = $makeReq($teacherUser, $taughtClass->id, [$studentInOther->id => 'Hadir']);
    $validator = Validator::make($invalidReq->all(), $invalidReq->rules(), $invalidReq->messages());
    $invalidReq->withValidator($validator);
    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('statuses'))->toBeTrue();

    // Valid student in taught class passes cleanly
    $validReq = $makeReq($teacherUser, $taughtClass->id, [$studentInTaught->id => 'Hadir']);
    $validValidator = Validator::make($validReq->all(), $validReq->rules(), $validReq->messages());
    $validReq->withValidator($validValidator);
    expect($validValidator->passes())->toBeTrue();
});

test('resource, grade, profile, and excel import form requests enforce authorization and unique/range rules', function () {
    $admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@unit.test', 'username' => 'admin.unit']);
    $teacherUser = User::factory()->create(['role' => 'teacher', 'email' => 'teacher@unit.test', 'username' => 'teacher.unit']);

    $class = SchoolClass::create(['code' => 'CLS-10', 'name' => '10 IPA 1', 'capacity' => 36]);
    $subject = Subject::create(['code' => 'MP-10', 'name' => 'Kimia', 'credit_hours' => 4]);
    $student = Student::create(['student_code' => 'STD-10', 'name' => 'Alya', 'class_id' => $class->id, 'status' => 'Aktif']);
    $teacher = Teacher::create(['user_id' => $teacherUser->id, 'subject_id' => $subject->id, 'teacher_code' => 'TCH-10', 'name' => 'Guru', 'phone' => '081234567890', 'status' => 'Aktif']);

    // Authorization matrix on FormRequests
    foreach ([StudentRequest::class, TeacherRequest::class, ClassRequest::class, SubjectRequest::class, UserRequest::class, GradeRequest::class, ExcelImportRequest::class] as $reqClass) {
        $adminReq = $reqClass::create('/', 'POST');
        $adminReq->setUserResolver(fn () => $admin);
        $teacherReq = $reqClass::create('/', 'POST');
        $teacherReq->setUserResolver(fn () => $teacherUser);

        expect($adminReq->authorize())->toBeTrue()
            ->and($teacherReq->authorize())->toBeFalse();
    }

    // ProfileUpdateRequest allows any authenticated user & ignores own email
    $profileReq = ProfileUpdateRequest::create('/profile', 'PUT', [
        'name' => 'Guru Update',
        'email' => 'teacher@unit.test',
        'phone' => '081234567890',
    ]);
    $profileReq->setUserResolver(fn () => $teacherUser);
    expect($profileReq->authorize())->toBeTrue()
        ->and(Validator::make($profileReq->all(), $profileReq->rules(), $profileReq->messages())->passes())->toBeTrue();

    // Update UserRequest ignores bound route user's email/username and permits optional empty password
    $userEditReq = UserRequest::create('/users/'.$teacherUser->slug, 'PUT', [
        'name' => 'Guru Edit',
        'username' => 'teacher.unit',
        'email' => 'teacher@unit.test',
        'password' => '',
        'role' => 'teacher',
        'status' => 'Aktif',
    ]);
    $route = new Route('PUT', '/users/{user}', fn () => null);
    $route->bind($userEditReq);
    $route->setParameter('user', $teacherUser);
    $userEditReq->setRouteResolver(fn () => $route);
    expect(Validator::make($userEditReq->all(), $userEditReq->rules(), $userEditReq->messages())->passes())->toBeTrue();

    // Update StudentRequest, TeacherRequest, ClassRequest, SubjectRequest ignore own unique code
    $studentRoute = new Route('PUT', '/students/{student}', fn () => null);
    $studentEditReq = StudentRequest::create('/', 'PUT', ['student_code' => 'STD-10', 'name' => 'Alya Edit', 'class_id' => $class->id, 'status' => 'Aktif']);
    $studentRoute->bind($studentEditReq);
    $studentRoute->setParameter('student', $student);
    $studentEditReq->setRouteResolver(fn () => $studentRoute);
    expect(Validator::make($studentEditReq->all(), $studentEditReq->rules())->passes())->toBeTrue();

    $teacherRoute = new Route('PUT', '/teachers/{teacher}', fn () => null);
    $teacherEditReq = TeacherRequest::create('/', 'PUT', ['teacher_code' => 'TCH-10', 'name' => 'Guru', 'subject_id' => $subject->id, 'class_ids' => [$class->id], 'phone' => '081234567890', 'status' => 'Aktif']);
    $teacherRoute->bind($teacherEditReq);
    $teacherRoute->setParameter('teacher', $teacher);
    $teacherEditReq->setRouteResolver(fn () => $teacherRoute);
    expect(Validator::make($teacherEditReq->all(), $teacherEditReq->rules())->passes())->toBeTrue();

    $classRoute = new Route('PUT', '/classes/{schoolClass}', fn () => null);
    $classEditReq = ClassRequest::create('/', 'PUT', ['code' => 'CLS-10', 'name' => '10 IPA 1', 'capacity' => 36]);
    $classRoute->bind($classEditReq);
    $classRoute->setParameter('schoolClass', $class);
    $classEditReq->setRouteResolver(fn () => $classRoute);
    expect(Validator::make($classEditReq->all(), $classEditReq->rules())->passes())->toBeTrue();

    $subjectRoute = new Route('PUT', '/subjects/{subject}', fn () => null);
    $subjectEditReq = SubjectRequest::create('/', 'PUT', ['code' => 'MP-10', 'name' => 'Kimia', 'credit_hours' => 4]);
    $subjectRoute->bind($subjectEditReq);
    $subjectRoute->setParameter('subject', $subject);
    $subjectEditReq->setRouteResolver(fn () => $subjectRoute);
    expect(Validator::make($subjectEditReq->all(), $subjectEditReq->rules())->passes())->toBeTrue();

    // GradeRequest & ExcelImportRequest invalid payload checks
    $gradeReq = GradeRequest::create('/grades', 'POST', ['student_id' => $student->id, 'subject_id' => $subject->id, 'semester' => 'Ganjil', 'score' => 105]);
    expect(Validator::make($gradeReq->all(), $gradeReq->rules(), $gradeReq->messages())->fails())->toBeTrue();

    $importReq = new ExcelImportRequest(['csv_file' => UploadedFile::fake()->create('data.csv', 10, 'text/csv')]);
    expect(Validator::make($importReq->all(), $importReq->rules(), $importReq->messages())->passes())->toBeTrue();
});
