<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubjectRequest;
use App\Models\ActivityLog;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(): View
    {
        $archivedCount = Subject::onlyArchived()->count();

        return view('subjects.index', compact('archivedCount'));
    }

    public function create(): View
    {
        $nextNumber = Subject::withArchived()->count() + 1;
        $nextCode = 'MP-'.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);

        return view('subjects.add', compact('nextCode'));
    }

    public function store(SubjectRequest $request): RedirectResponse
    {
        $subject = Subject::create($request->validated());

        ActivityLog::record(
            'Mata pelajaran baru ditambahkan',
            'subjects',
            $request->user()->name.' · '.$subject->name.' ('.$subject->code.')',
            'success'
        );

        return redirect()->route('subjects.index')
            ->with('toast', 'Mata pelajaran '.$subject->name.' berhasil ditambahkan!');
    }

    public function edit(Subject $subject): View
    {
        return view('subjects.edit', compact('subject'));
    }

    public function update(SubjectRequest $request, Subject $subject): RedirectResponse
    {
        $subject->update($request->validated());

        ActivityLog::record(
            'Data mata pelajaran diperbarui',
            'subjects',
            $request->user()->name.' · '.$subject->name.' ('.$subject->code.')',
            'primary'
        );

        return redirect()->route('subjects.index')
            ->with('toast', 'Mata pelajaran '.$subject->name.' berhasil diperbarui!');
    }

    public function archive(Request $request, Subject $subject): RedirectResponse
    {
        if (! $request->user()?->isAdmin()) {
            abort(403);
        }

        $subject->archive();

        ActivityLog::record(
            'Mata pelajaran di-archive',
            'subjects',
            $request->user()->name.' · '.$subject->name,
            'error'
        );

        return redirect()->route('subjects.index')
            ->with('toast', 'Mata pelajaran '.$subject->name.' telah dipindahkan ke Archive.');
    }
}
