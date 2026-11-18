<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClassRequest;
use App\Models\ActivityLog;
use App\Models\SchoolClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassController extends Controller
{
    public function index(): View
    {
        $archivedCount = SchoolClass::onlyArchived()->count();

        return view('classes.index', compact('archivedCount'));
    }

    public function show(Request $request, SchoolClass $schoolClass): View
    {
        $user = $request->user();
        if ($user->isStudent()) {
            $student = $user->student;
            if (! $student || $student->class_id !== $schoolClass->id) {
                abort(403, 'Anda hanya dapat mengakses informasi rombongan belajar kelas Anda sendiri.');
            }
        }

        $schoolClass->load([
            'students' => fn ($q) => $q->with('user')->orderBy('name'),
            'teachers' => fn ($q) => $q->with('subject')->orderBy('name'),
            'schedules' => fn ($q) => $q->with(['subject', 'teacher'])->orderBy('day_of_week')->orderBy('start_time'),
        ]);

        $todayAttendances = $schoolClass->attendances()
            ->whereDate('date', today())
            ->get();

        $attendanceSummary = [
            'Hadir' => $todayAttendances->where('status', 'Hadir')->count(),
            'Izin' => $todayAttendances->where('status', 'Izin')->count(),
            'Sakit' => $todayAttendances->where('status', 'Sakit')->count(),
            'Alpa' => $todayAttendances->where('status', 'Alpa')->count(),
        ];

        return view('classes.show', [
            'schoolClass' => $schoolClass,
            'todayAttendances' => $todayAttendances,
            'attendanceSummary' => $attendanceSummary,
            'title' => 'Rombel '.$schoolClass->name,
            'breadcrumbs' => [
                ['label' => 'Data Kelas', 'url' => route('classes.index')],
                ['label' => $schoolClass->name],
            ],
        ]);
    }

    public function create(): View
    {
        $nextNumber = SchoolClass::withArchived()->count() + 1;
        $nextCode = 'CLS-'.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);

        return view('classes.add', compact('nextCode'));
    }

    public function store(ClassRequest $request): RedirectResponse
    {
        $schoolClass = SchoolClass::create($request->validated());

        ActivityLog::record(
            'Kelas baru ditambahkan',
            'classes',
            $request->user()->name.' · Kelas '.$schoolClass->name.' ('.$schoolClass->code.')',
            'success'
        );

        return redirect()->route('classes.index')
            ->with('toast', 'Kelas '.$schoolClass->name.' berhasil ditambahkan!');
    }

    public function edit(SchoolClass $schoolClass): View
    {
        return view('classes.edit', compact('schoolClass'));
    }

    public function update(ClassRequest $request, SchoolClass $schoolClass): RedirectResponse
    {
        $schoolClass->update($request->validated());

        ActivityLog::record(
            'Data kelas diperbarui',
            'classes',
            $request->user()->name.' · Kelas '.$schoolClass->name.' ('.$schoolClass->code.')',
            'primary'
        );

        return redirect()->route('classes.index')
            ->with('toast', 'Data kelas '.$schoolClass->name.' berhasil diperbarui!');
    }

    public function archive(Request $request, SchoolClass $schoolClass): RedirectResponse
    {
        if (! $request->user()?->isAdmin()) {
            abort(403);
        }

        $schoolClass->archive();

        ActivityLog::record(
            'Data kelas di-archive',
            'classes',
            $request->user()->name.' · Kelas '.$schoolClass->name,
            'error'
        );

        return redirect()->route('classes.index')
            ->with('toast', 'Kelas '.$schoolClass->name.' telah dipindahkan ke Archive.');
    }
}
