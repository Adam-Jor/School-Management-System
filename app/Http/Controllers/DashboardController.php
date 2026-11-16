<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $stats = Cache::remember('edudash_dashboard_stats', 180, function () {
            return [
                'users' => User::count(),
                'students' => Student::count(),
                'students_active' => Student::where('status', 'Aktif')->count(),
                'teachers' => Teacher::count(),
                'classes' => SchoolClass::count(),
                'subjects' => Subject::count(),
            ];
        });

        $search = trim((string) $request->query('q', ''));
        $statusFilter = trim((string) $request->query('status', ''));

        $studentsQuery = Student::query()
            ->with(['schoolClass', 'user'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('student_code', 'like', "%{$search}%");
                });
            })
            ->when($statusFilter !== '', function ($q) use ($statusFilter) {
                $q->where('status', $statusFilter);
            })
            ->latest('id');

        $latestStudents = $studentsQuery->paginate(5)->withQueryString();

        $activities = ActivityLog::query()
            ->with('user')
            ->latest('id')
            ->limit(5)
            ->get();

        $classesWithCount = SchoolClass::withCount('students')->orderBy('name')->get();
        $statusCounts = [
            'Aktif' => Student::where('status', 'Aktif')->count(),
            'Pindah' => Student::where('status', 'Pindah')->count(),
            'Menunggu' => Student::where('status', 'Menunggu')->count(),
        ];

        $chartData = [
            'labels' => ['Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
            'values' => [845, 868, 892, 920, 945, 972, 1012, 1042, 1058, 1074, 1090, 1105],
            'class_labels' => $classesWithCount->pluck('name')->values()->all(),
            'class_values' => $classesWithCount->pluck('students_count')->values()->all(),
            'status_labels' => array_keys($statusCounts),
            'status_values' => array_values($statusCounts),
        ];

        $globalResults = null;
        $globalQuery = trim((string) $request->query('global_q', ''));
        if ($globalQuery !== '') {
            $globalResults = [
                'students' => Student::with('schoolClass')
                    ->where('name', 'like', "%{$globalQuery}%")
                    ->orWhere('student_code', 'like', "%{$globalQuery}%")
                    ->limit(5)
                    ->get(),
                'teachers' => Teacher::with('subject')
                    ->where('name', 'like', "%{$globalQuery}%")
                    ->orWhere('teacher_code', 'like', "%{$globalQuery}%")
                    ->limit(5)
                    ->get(),
                'classes' => SchoolClass::where('name', 'like', "%{$globalQuery}%")
                    ->orWhere('code', 'like', "%{$globalQuery}%")
                    ->limit(5)
                    ->get(),
            ];
        }

        return view('dashboard.index', compact(
            'stats',
            'latestStudents',
            'activities',
            'chartData',
            'search',
            'statusFilter',
            'globalQuery',
            'globalResults'
        ));
    }
}
