<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArchiveController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'students');

        $archivedStudents = Student::onlyArchived()->with(['schoolClass', 'user'])->latest('updated_at')->get();
        $archivedTeachers = Teacher::onlyArchived()->with(['subject', 'user'])->latest('updated_at')->get();
        $archivedClasses = SchoolClass::onlyArchived()->latest('updated_at')->get();
        $archivedSubjects = Subject::onlyArchived()->latest('updated_at')->get();
        $archivedUsers = User::onlyArchived()->latest('updated_at')->get();

        return view('archive.index', compact(
            'tab',
            'archivedStudents',
            'archivedTeachers',
            'archivedClasses',
            'archivedSubjects',
            'archivedUsers'
        ));
    }

    public function restore(Request $request, string $type, int $id): RedirectResponse
    {
        $modelMap = [
            'students' => Student::class,
            'teachers' => Teacher::class,
            'classes' => SchoolClass::class,
            'subjects' => Subject::class,
            'users' => User::class,
        ];

        $modelClass = $modelMap[$type] ?? null;
        if (! $modelClass) {
            abort(404);
        }

        $record = $modelClass::onlyArchived()->findOrFail($id);
        $record->restoreFromArchive();

        ActivityLog::record(
            'Data dipulihkan dari Archive (Restore)',
            $type,
            $request->user()->name.' · '.($record->name ?? $record->code ?? "#{$id}"),
            'success'
        );

        return redirect()->route('archive.index', ['tab' => $type])
            ->with('toast', 'Data '.($record->name ?? '').' berhasil dipulihkan ke daftar aktif!');
    }
}
