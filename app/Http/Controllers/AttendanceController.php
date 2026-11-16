<?php

namespace App\Http\Controllers;

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
        $classes = SchoolClass::orderBy('name')->get();
        $selectedClassId = (int) ($request->query('class_id') ?: ($classes->first()?->id ?? 0));
        $selectedDate = $request->query('date', now()->toDateString());
        $selectedMonth = $request->query('month', now()->format('Y-m'));

        $students = Student::query()
            ->where('class_id', $selectedClassId)
            ->orderBy('name')
            ->get();

        $dailyAttendances = Attendance::query()
            ->where('class_id', $selectedClassId)
            ->whereDate('date', $selectedDate)
            ->get()
            ->keyBy('student_id');

        // Rekap bulanan per siswa pada kelas terpilih
        [$year, $month] = array_pad(explode('-', $selectedMonth), 2, now()->format('m'));
        $monthlyRecords = Attendance::query()
            ->where('class_id', $selectedClassId)
            ->whereYear('date', (int) $year)
            ->whereMonth('date', (int) $month)
            ->get()
            ->groupBy('student_id');

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
            'monthlySummary'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_id' => ['required', 'exists:tbl_classes,id'],
            'date' => ['required', 'date'],
            'statuses' => ['required', 'array'],
            'statuses.*' => ['required', 'in:Hadir,Izin,Sakit,Alpa'],
            'notes' => ['nullable', 'array'],
        ]);

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
