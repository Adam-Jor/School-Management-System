<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class GradeController extends Controller
{
    public function index(Request $request): View
    {
        $classes = SchoolClass::orderBy('name')->get();
        $subjects = Subject::orderBy('code')->get();
        $classId = $request->query('class_id', '');
        $subjectId = $request->query('subject_id', '');
        $semester = $request->query('semester', 'Ganjil 2026/2027');

        $grades = Grade::query()
            ->with(['student.schoolClass', 'subject'])
            ->when($classId !== '', function ($q) use ($classId) {
                $q->whereHas('student', fn ($sub) => $sub->where('class_id', $classId));
            })
            ->when($subjectId !== '', fn ($q) => $q->where('subject_id', $subjectId))
            ->when($semester !== '', fn ($q) => $q->where('semester', $semester))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $students = Student::with('schoolClass')->orderBy('name')->get();

        return view('grades.index', compact(
            'grades',
            'classes',
            'subjects',
            'students',
            'classId',
            'subjectId',
            'semester'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:tbl_students,id'],
            'subject_id' => ['required', 'exists:tbl_subjects,id'],
            'semester' => ['required', 'string', 'max:40'],
            'score' => ['required', 'numeric', 'min:0', 'max:100'],
        ], [
            'student_id.required' => 'Siswa wajib dipilih.',
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'score.required' => 'Nilai angka (0–100) wajib diisi.',
        ]);

        $score = (float) $validated['score'];
        $predicate = match (true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            default => 'D',
        };

        $grade = Grade::updateOrCreate(
            [
                'student_id' => $validated['student_id'],
                'subject_id' => $validated['subject_id'],
                'semester' => $validated['semester'],
            ],
            [
                'score' => $score,
                'predicate' => $predicate,
                'archived' => 0,
            ]
        );

        $student = Student::find($validated['student_id']);
        $subject = Subject::find($validated['subject_id']);

        ActivityLog::record(
            'Nilai akademik siswa disimpan',
            'grades',
            $request->user()->name.' · '.($student?->name ?? '-').' ('.($subject?->name ?? '-').': '.$score.')',
            'primary'
        );

        return redirect()->route('grades.index')
            ->with('toast', 'Nilai '.($subject?->name ?? '').' untuk '.($student?->name ?? '').' berhasil disimpan!');
    }

    public function reportPdf(Student $student): Response
    {
        $student->load(['schoolClass', 'user']);
        $grades = Grade::with('subject')
            ->where('student_id', $student->id)
            ->get();

        $attendanceSummary = [
            'Hadir' => Attendance::where('student_id', $student->id)->where('status', 'Hadir')->count(),
            'Izin' => Attendance::where('student_id', $student->id)->where('status', 'Izin')->count(),
            'Sakit' => Attendance::where('student_id', $student->id)->where('status', 'Sakit')->count(),
            'Alpa' => Attendance::where('student_id', $student->id)->where('status', 'Alpa')->count(),
        ];

        $averageScore = $grades->count() > 0 ? round((float) $grades->avg('score'), 2) : 0;

        ActivityLog::record(
            'Cetak Rapor PDF Siswa',
            'grades',
            (auth()->user()?->name ?? 'User').' · Rapor '.$student->name.' ('.$student->student_code.')',
            'success'
        );

        $pdf = Pdf::loadView('grades.report-pdf', compact(
            'student',
            'grades',
            'attendanceSummary',
            'averageScore'
        ));

        return $pdf->download('rapor-'.$student->student_code.'-'.now()->format('YmdHis').'.pdf');
    }
}
