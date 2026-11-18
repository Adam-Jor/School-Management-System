<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScheduleRequest;
use App\Models\AcademicPeriod;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $isTeacher = $user?->isTeacher();
        $isStudent = $user?->isStudent();

        $teacher = $isTeacher ? $user->teacher : null;
        $student = $isStudent ? $user->student : null;

        $classId = $request->query('class_id');
        $day = $request->query('day');

        $query = Schedule::query()
            ->with(['schoolClass', 'subject', 'teacher', 'academicPeriod'])
            ->orderByRaw("FIELD(day_of_week, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu')")
            ->orderBy('start_time');

        if ($isStudent && $student) {
            $query->where('class_id', $student->class_id);
        } elseif ($isTeacher && $teacher) {
            if ($classId) {
                $query->where('class_id', $classId);
            } else {
                $query->where('teacher_id', $teacher->id);
            }
        } elseif ($classId) {
            $query->where('class_id', $classId);
        }

        if ($day) {
            $query->where('day_of_week', $day);
        }

        $schedules = $query->get()->groupBy('day_of_week');

        return view('schedules.index', [
            'title' => 'Jadwal Pelajaran',
            'breadcrumbs' => [
                ['label' => 'Jadwal Pelajaran'],
            ],
            'schedules' => $schedules,
            'classes' => SchoolClass::orderBy('name')->get(),
            'teachers' => Teacher::orderBy('name')->get(),
            'subjects' => Subject::orderBy('name')->get(),
            'periods' => AcademicPeriod::where('is_active', true)->get(),
            'selectedClassId' => $classId,
            'selectedDay' => $day,
            'isTeacher' => $isTeacher,
            'isStudent' => $isStudent,
        ]);
    }

    public function store(ScheduleRequest $request): RedirectResponse
    {
        Schedule::create($request->validated());

        return redirect()->route('schedules.index')
            ->with('toast', 'Jadwal pelajaran berhasil ditambahkan');
    }

    public function archive(Schedule $schedule): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $schedule->archive();

        return redirect()->route('schedules.index')
            ->with('toast', 'Jadwal pelajaran berhasil dipindahkan ke arsip')
            ->with('toast_type', 'warning');
    }
}
