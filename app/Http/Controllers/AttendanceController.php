<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceRequest;
use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $selectedDate = (string) $request->query('date', now()->toDateString());
        $selectedMonth = (string) $request->query('month', now()->format('Y-m'));

        if ($user->isAdmin()) {
            $classes = SchoolClass::orderBy('name')->get();
            $selectedClassId = (int) ($request->query('class_id') ?: ($classes->first()?->id ?? 0));
            $canRecordAttendance = $selectedClassId > 0 && $classes->contains('id', $selectedClassId);
            $students = Student::query()
                ->where('class_id', $selectedClassId)
                ->orderBy('name')
                ->get();
        } elseif ($user->isTeacher()) {
            $teacher = $user->teacher;
            $classes = $teacher
                ? $teacher->schoolClasses()->orderBy('name')->get()
                : collect();

            if ($request->filled('class_id')) {
                $requestedClassId = (int) $request->query('class_id');
                if (! $classes->contains('id', $requestedClassId)) {
                    abort(403, 'Akses ditolak. Anda hanya dapat mengelola absensi untuk kelas yang sedang Anda ajar.');
                }
            }

            $selectedClassId = (int) ($request->query('class_id') ?: ($classes->first()?->id ?? 0));
            $canRecordAttendance = $selectedClassId > 0 && $classes->contains('id', $selectedClassId);
            $students = $selectedClassId > 0
                ? Student::query()->where('class_id', $selectedClassId)->orderBy('name')->get()
                : collect();
        } else {
            $ownStudent = $user->student?->load('schoolClass');
            $classes = $ownStudent?->schoolClass ? collect([$ownStudent->schoolClass]) : collect();
            $selectedClassId = (int) ($ownStudent?->class_id ?? 0);
            $canRecordAttendance = false;
            $students = $ownStudent ? collect([$ownStudent]) : collect();
        }

        $studentIds = $students->pluck('id');

        $dailyAttendances = $selectedClassId > 0 && $studentIds->isNotEmpty()
            ? Attendance::query()
                ->where('class_id', $selectedClassId)
                ->whereIn('student_id', $studentIds)
                ->whereDate('date', $selectedDate)
                ->get()
                ->keyBy('student_id')
            : collect();

        [$year, $month] = array_pad(explode('-', $selectedMonth), 2, now()->format('m'));
        $monthlyRecords = $selectedClassId > 0 && $studentIds->isNotEmpty()
            ? Attendance::query()
                ->where('class_id', $selectedClassId)
                ->whereIn('student_id', $studentIds)
                ->whereYear('date', (int) $year)
                ->whereMonth('date', (int) $month)
                ->get()
                ->groupBy('student_id')
            : collect();

        $monthlySummary = [];
        foreach ($students as $st) {
            $group = $monthlyRecords->get($st->id, collect());
            $monthlySummary[$st->id] = [
                'Hadir' => $group->where('status', 'Hadir')->count(),
                'Izin' => $group->where('status', 'Izin')->count(),
                'Sakit' => $group->where('status', 'Sakit')->count(),
                'Alpa' => $group->where('status', 'Alpa')->count(),
            ];
        }

        return view('attendances.index', compact(
            'classes',
            'selectedClassId',
            'selectedDate',
            'selectedMonth',
            'students',
            'dailyAttendances',
            'monthlySummary',
            'canRecordAttendance'
        ));
    }

    public function store(AttendanceRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        foreach ($validated['statuses'] as $studentId => $status) {
            Attendance::updateOrCreate(
                [
                    'student_id' => (int) $studentId,
                    'class_id' => (int) $validated['class_id'],
                    'date' => $validated['date'],
                ],
                [
                    'status' => $status,
                    'notes' => $validated['notes'][$studentId] ?? null,
                    'archived' => 0,
                ]
            );
        }

        $schoolClass = SchoolClass::find($validated['class_id']);
        ActivityLog::record(
            'Absensi harian kelas disimpan',
            'attendances',
            $request->user()->name.' · Kelas '.($schoolClass?->name ?? '-').' ('.format_date($validated['date']).')',
            'success'
        );

        return redirect()->route('attendances.index', [
            'class_id' => $validated['class_id'],
            'date' => $validated['date'],
        ])->with('toast', 'Absensi harian kelas '.($schoolClass?->name ?? '').' berhasil disimpan!');
    }
}
