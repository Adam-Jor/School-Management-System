<?php

namespace App\Http\Controllers;

use App\Http\Requests\GradeRequest;
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
        $user = $request->user();
        $isStudent = $user->isStudent();
        $isTeacher = $user->isTeacher();
        $teacher = $isTeacher ? $user->teacher?->load(['schoolClasses', 'subject']) : null;
        $ownStudent = $isStudent ? $user->student?->load('schoolClass') : null;

        $classes = $isStudent
            ? ($ownStudent?->schoolClass ? collect([$ownStudent->schoolClass]) : collect())
            : ($isTeacher && $teacher ? $teacher->schoolClasses : SchoolClass::orderBy('name')->get());

        $subjects = $isTeacher && $teacher && $teacher->subject_id
            ? Subject::where('id', $teacher->subject_id)->get()
            : Subject::orderBy('code')->get();

        $classId = $isStudent ? (string) ($ownStudent?->class_id ?? '') : (string) $request->query('class_id', '');
        $subjectId = (string) $request->query('subject_id', '');
        $semester = (string) $request->query('semester', 'Ganjil 2026/2027');

        $grades = Grade::query()
            ->with(['student.schoolClass', 'subject'])
            ->when($isStudent, fn ($q) => $q->where('student_id', $ownStudent?->id ?? 0))
            ->when($isTeacher && $teacher, function ($q) use ($teacher) {
                $taughtClassIds = $teacher->schoolClasses->pluck('id');
                $q->where(function ($sub) use ($taughtClassIds, $teacher) {
                    if ($taughtClassIds->isNotEmpty()) {
                        $sub->whereHas('student', fn ($s) => $s->whereIn('class_id', $taughtClassIds));
                    }
                    if ($teacher->subject_id) {
                        $sub->orWhere('subject_id', $teacher->subject_id);
                    }
                });
            })
            ->when(! $isStudent && $classId !== '', function ($q) use ($classId) {
                $q->whereHas('student', fn ($sub) => $sub->where('class_id', $classId));
            })
            ->when($subjectId !== '', fn ($q) => $q->where('subject_id', $subjectId))
            ->when($semester !== '', fn ($q) => $q->where('semester', $semester))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $students = $isStudent
            ? ($ownStudent ? collect([$ownStudent]) : collect())
            : ($isTeacher && $teacher
                ? Student::with('schoolClass')->whereIn('class_id', $teacher->schoolClasses->pluck('id'))->orderBy('name')->get()
                : Student::with('schoolClass')->orderBy('name')->get());

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

    public function store(GradeRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $score = (float) $validated['score'];
        $predicate = match (true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            default => 'D',
        };

        Grade::updateOrCreate(
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

    public function reportPdf(Request $request, Student $student): Response
    {
        if ($request->user()?->isStudent() && $student->user_id !== $request->user()->id) {
            abort(403, 'Anda hanya diizinkan mengunduh rapor akademik milik Anda sendiri.');
        }

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
