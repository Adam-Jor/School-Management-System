<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Grade;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        return match ($request->user()->role) {
            'admin' => $this->buildAdminDashboard($request),
            'teacher' => $this->buildTeacherDashboard($request),
            default => $this->buildStudentDashboard($request),
        };
    }

    public function admin(Request $request): View
    {
        return $this->buildAdminDashboard($request);
    }

    public function teacher(Request $request): View
    {
        return $this->buildTeacherDashboard($request);
    }

    public function student(Request $request): View
    {
        return $this->buildStudentDashboard($request);
    }

    protected function buildAdminDashboard(Request $request): View
    {
        $stats = edudash_dashboard_stats();

        $search = trim((string) $request->query('q', ''));
        $statusFilter = trim((string) $request->query('status', ''));

        $latestStudents = Student::query()
            ->with(['schoolClass', 'user'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('student_code', 'like', "%{$search}%");
                });
            })
            ->when($statusFilter !== '', fn ($q) => $q->where('status', $statusFilter))
            ->latest('id')
            ->paginate(5)
            ->withQueryString();

        $activities = Activity::query()
            ->with('causer')
            ->latest('id')
            ->limit(5)
            ->get();

        $windowStart = now()->startOfMonth()->subMonths(11);
        $studentDates = Student::pluck('created_at');

        $growthLabels = [];
        $growthValues = [];
        for ($i = 0; $i < 12; $i++) {
            $month = $windowStart->copy()->addMonths($i);
            $monthEnd = $month->copy()->endOfMonth();
            $growthLabels[] = indonesian_month_short($month->month).' '.$month->format('y');
            $growthValues[] = $studentDates->filter(function ($date) use ($monthEnd) {
                return $date && Carbon::parse($date)->lte($monthEnd);
            })->count();
        }

        $classesWithCount = SchoolClass::withCount('students')->orderBy('name')->get();
        $statusCounts = [
            'Aktif' => Student::where('status', 'Aktif')->count(),
            'Pindah' => Student::where('status', 'Pindah')->count(),
            'Menunggu' => Student::where('status', 'Menunggu')->count(),
        ];

        $chartData = [
            'labels' => $growthLabels,
            'values' => $growthValues,
            'class_labels' => $classesWithCount->pluck('name')->values()->all(),
            'class_values' => $classesWithCount->pluck('students_count')->values()->all(),
            'status_labels' => array_keys($statusCounts),
            'status_values' => array_values($statusCounts),
        ];

        $globalQuery = trim((string) $request->query('global_q', ''));
        $globalResults = $globalQuery !== ''
            ? [
                'students' => Student::with('schoolClass')
                    ->where(function ($q) use ($globalQuery) {
                        $q->where('name', 'like', "%{$globalQuery}%")
                            ->orWhere('student_code', 'like', "%{$globalQuery}%");
                    })
                    ->limit(5)
                    ->get(),
                'teachers' => Teacher::with('subject')
                    ->where(function ($q) use ($globalQuery) {
                        $q->where('name', 'like', "%{$globalQuery}%")
                            ->orWhere('teacher_code', 'like', "%{$globalQuery}%");
                    })
                    ->limit(5)
                    ->get(),
                'classes' => SchoolClass::where(function ($q) use ($globalQuery) {
                    $q->where('name', 'like', "%{$globalQuery}%")
                        ->orWhere('code', 'like', "%{$globalQuery}%");
                })
                    ->limit(5)
                    ->get(),
            ]
            : null;

        return view('dashboard.admin', compact(
            'stats',
            'latestStudents',
            'activities',
            'chartData',
            'search',
            'statusFilter',
            'globalResults',
            'globalQuery'
        ));
    }

    protected function buildTeacherDashboard(Request $request): View
    {
        $user = $request->user();
        $teacher = $user->teacher?->load([
            'subject',
            'schoolClasses' => fn ($q) => $q->withCount('students')->orderBy('name'),
        ]);

        $taughtClasses = $teacher?->schoolClasses ?? collect();
        $taughtClassIds = $taughtClasses->pluck('id');
        $search = trim((string) $request->query('q', ''));

        $classStudents = $taughtClassIds->isNotEmpty()
            ? Student::query()
                ->with(['schoolClass', 'user'])
                ->whereIn('class_id', $taughtClassIds)
                ->when($search !== '', function ($q) use ($search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('student_code', 'like', "%{$search}%");
                    });
                })
                ->orderBy('class_id')
                ->orderBy('name')
                ->paginate(6)
                ->withQueryString()
            : Student::whereRaw('1 = 0')->paginate(6);

        $todayRecords = $taughtClassIds->isNotEmpty()
            ? Attendance::query()
                ->whereIn('class_id', $taughtClassIds)
                ->whereDate('date', today())
                ->get()
                ->groupBy('class_id')
            : collect();

        $todayAttendanceMap = [];
        foreach ($taughtClasses as $cls) {
            $group = $todayRecords->get($cls->id, collect());
            $todayAttendanceMap[$cls->id] = [
                'recorded' => $group->count(),
                'present' => $group->where('status', 'Hadir')->count(),
            ];
        }

        $teacherStats = [
            'classes' => $taughtClasses->count(),
            'students' => $taughtClassIds->isNotEmpty() ? Student::whereIn('class_id', $taughtClassIds)->count() : 0,
            'today_recorded' => $todayRecords->flatten()->count(),
            'today_present' => $todayRecords->flatten()->where('status', 'Hadir')->count(),
            'subjects' => Subject::count(),
        ];

        $globalQuery = trim((string) $request->query('global_q', ''));
        $globalResults = $globalQuery !== ''
            ? [
                'students' => Student::with('schoolClass')
                    ->where(function ($q) use ($globalQuery) {
                        $q->where('name', 'like', "%{$globalQuery}%")
                            ->orWhere('student_code', 'like', "%{$globalQuery}%");
                    })
                    ->limit(5)
                    ->get(),
                'teachers' => collect(),
                'classes' => SchoolClass::where(function ($q) use ($globalQuery) {
                    $q->where('name', 'like', "%{$globalQuery}%")
                        ->orWhere('code', 'like', "%{$globalQuery}%");
                })
                    ->limit(5)
                    ->get(),
            ]
            : null;

        $daysIndo = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        $todayName = $daysIndo[now()->dayOfWeekIso] ?? 'Senin';
        $todaySchedules = $teacher
            ? Schedule::with(['schoolClass', 'subject'])
                ->where('teacher_id', $teacher->id)
                ->where('day_of_week', $todayName)
                ->orderBy('start_time')
                ->get()
            : collect();

        return view('dashboard.teacher', compact(
            'teacher',
            'taughtClasses',
            'classStudents',
            'todayAttendanceMap',
            'teacherStats',
            'todaySchedules',
            'todayName',
            'search',
            'globalResults',
            'globalQuery'
        ));
    }

    protected function buildStudentDashboard(Request $request): View
    {
        $user = $request->user();
        $student = $user->student?->load(['schoolClass', 'user']);

        $attendanceSummary = [
            'Hadir' => $student ? Attendance::where('student_id', $student->id)->where('status', 'Hadir')->count() : 0,
            'Izin' => $student ? Attendance::where('student_id', $student->id)->where('status', 'Izin')->count() : 0,
            'Sakit' => $student ? Attendance::where('student_id', $student->id)->where('status', 'Sakit')->count() : 0,
            'Alpa' => $student ? Attendance::where('student_id', $student->id)->where('status', 'Alpa')->count() : 0,
        ];

        $recentAttendances = $student
            ? Attendance::with('schoolClass')
                ->where('student_id', $student->id)
                ->latest('date')
                ->limit(6)
                ->get()
            : collect();

        $grades = $student
            ? Grade::with('subject')
                ->where('student_id', $student->id)
                ->latest('id')
                ->get()
            : collect();

        $averageScore = $grades->count() > 0 ? round((float) $grades->avg('score'), 2) : 0;
        $totalClasses = SchoolClass::count();

        $globalQuery = trim((string) $request->query('global_q', ''));
        $globalResults = $globalQuery !== ''
            ? [
                'students' => $student && (
                    str_contains(mb_strtolower($student->name), mb_strtolower($globalQuery))
                    || str_contains(mb_strtolower($student->student_code), mb_strtolower($globalQuery))
                )
                    ? collect([$student])
                    : collect(),
                'teachers' => collect(),
                'classes' => SchoolClass::where(function ($q) use ($globalQuery) {
                    $q->where('name', 'like', "%{$globalQuery}%")
                        ->orWhere('code', 'like', "%{$globalQuery}%");
                })
                    ->limit(5)
                    ->get(),
            ]
            : null;

        $daysIndo = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        $todayName = $daysIndo[now()->dayOfWeekIso] ?? 'Senin';
        $todaySchedules = $student && $student->class_id
            ? Schedule::with(['subject', 'teacher'])
                ->where('class_id', $student->class_id)
                ->where('day_of_week', $todayName)
                ->orderBy('start_time')
                ->get()
            : collect();

        return view('dashboard.student', compact(
            'student',
            'attendanceSummary',
            'recentAttendances',
            'grades',
            'averageScore',
            'totalClasses',
            'todaySchedules',
            'todayName',
            'globalResults',
            'globalQuery'
        ));
    }
}
