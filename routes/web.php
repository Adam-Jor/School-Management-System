<?php

use App\Http\Controllers\AcademicPeriodController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DesignSystemController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard (Admin: Full, Teacher: View, Student: View) — renders role-separated dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])
        ->middleware('role:admin')
        ->name('admin.dashboard');
    Route::get('/teacher/dashboard', [DashboardController::class, 'teacher'])
        ->middleware('role:teacher')
        ->name('teacher.dashboard');
    Route::get('/student/dashboard', [DashboardController::class, 'student'])
        ->middleware('role:student')
        ->name('student.dashboard');

    // Profile User (Admin: Edit, Teacher: Edit, Student: Edit)
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Manajemen Students (Admin: CRUD, Teacher: View, Student: View data sendiri)
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');

    // Manajemen Classes (Admin: CRUD, Teacher: View, Student: View)
    Route::get('/classes', [ClassController::class, 'index'])->name('classes.index');

    // Manajemen Subjects (Admin: CRUD, Teacher: View, Student: -)
    Route::get('/subjects', [SubjectController::class, 'index'])
        ->middleware('role:admin,teacher')
        ->name('subjects.index');

    // Absensi Siswa (Admin: Full, Teacher: kelas yang diajar, Student: data sendiri)
    Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances.index');
    Route::post('/attendances', [AttendanceController::class, 'store'])
        ->middleware('role:admin,teacher')
        ->name('attendances.store');

    // Nilai & Rapor (Admin & Teacher: Input/Update, Student: data & rapor sendiri)
    Route::get('/grades', [GradeController::class, 'index'])->name('grades.index');
    Route::post('/grades', [GradeController::class, 'store'])
        ->middleware('role:admin,teacher')
        ->name('grades.store');
    Route::get('/grades/report/{student:slug}/pdf', [GradeController::class, 'reportPdf'])->name('grades.report.pdf');

    // Jadwal Pelajaran Mingguan (Timetable)
    Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');

    // Academic Period Switcher
    Route::post('/academic-periods/switch', [AcademicPeriodController::class, 'switch'])->name('academic-periods.switch');

    // Living Styleguide & Design System
    Route::get('/design-system', [DesignSystemController::class, 'index'])->name('design-system.index');

    // Admin-only CRUD Routes (Users, Teachers, Students CRUD, Classes CRUD, Subjects CRUD, Schedules Admin, Archive)
    Route::middleware('role:admin')->group(function () {
        // Schedules Admin
        Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
        Route::patch('/schedules/{schedule}/archive', [ScheduleController::class, 'archive'])->name('schedules.archive');
        // Students CRUD
        Route::get('/students/add', [StudentController::class, 'create'])->name('students.create');
        Route::post('/students', [StudentController::class, 'store'])->name('students.store');
        Route::post('/students/import', [StudentController::class, 'import'])->name('students.import');
        Route::get('/students/{student:slug}/edit', [StudentController::class, 'edit'])->name('students.edit');
        Route::put('/students/{student:slug}', [StudentController::class, 'update'])->name('students.update');
        Route::patch('/students/{student:slug}/archive', [StudentController::class, 'archive'])->name('students.archive');

        // Teachers (Admin: CRUD, Teacher: -, Student: -)
        Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
        Route::get('/teachers/add', [TeacherController::class, 'create'])->name('teachers.create');
        Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
        Route::post('/teachers/import', [TeacherController::class, 'import'])->name('teachers.import');
        Route::get('/teachers/{teacher:slug}/edit', [TeacherController::class, 'edit'])->name('teachers.edit');
        Route::put('/teachers/{teacher:slug}', [TeacherController::class, 'update'])->name('teachers.update');
        Route::patch('/teachers/{teacher:slug}/archive', [TeacherController::class, 'archive'])->name('teachers.archive');

        // Classes CRUD
        Route::get('/classes/add', [ClassController::class, 'create'])->name('classes.create');
        Route::post('/classes', [ClassController::class, 'store'])->name('classes.store');
        Route::get('/classes/{schoolClass:slug}/edit', [ClassController::class, 'edit'])->name('classes.edit');
        Route::put('/classes/{schoolClass:slug}', [ClassController::class, 'update'])->name('classes.update');
        Route::patch('/classes/{schoolClass:slug}/archive', [ClassController::class, 'archive'])->name('classes.archive');

        // Subjects CRUD
        Route::get('/subjects/add', [SubjectController::class, 'create'])->name('subjects.create');
        Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
        Route::get('/subjects/{subject:slug}/edit', [SubjectController::class, 'edit'])->name('subjects.edit');
        Route::put('/subjects/{subject:slug}', [SubjectController::class, 'update'])->name('subjects.update');
        Route::patch('/subjects/{subject:slug}/archive', [SubjectController::class, 'archive'])->name('subjects.archive');

        // Users CRUD (Admin: CRUD, Teacher: -, Student: -)
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/add', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user:slug}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user:slug}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user:slug}/archive', [UserController::class, 'archive'])->name('users.archive');

        // Archive / Trash Management
        Route::get('/archive', [ArchiveController::class, 'index'])->name('archive.index');
        Route::patch('/archive/{type}/{id}/restore', [ArchiveController::class, 'restore'])->name('archive.restore');
    });

    // Detail Kelas Interaktif (setelah /classes/add agar tidak tertabrak route model binding)
    Route::get('/classes/{schoolClass:slug}', [ClassController::class, 'show'])->name('classes.show');
});
