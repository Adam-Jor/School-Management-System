<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $stats = edudash_dashboard_stats();

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

        $activities = Activity::query()
            ->with('causer')
            ->latest('id')
            ->limit(5)
            ->get();

        // Hitung tren kumulatif siswa 12 bulan terakhir langsung dari tbl_students.created_at
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
