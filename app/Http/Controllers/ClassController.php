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
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $classes = SchoolClass::query()
            ->withCount('students')
            ->when($search !== '', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(8)
            ->withQueryString();

        $archivedCount = SchoolClass::onlyArchived()->count();

        return view('classes.index', compact('classes', 'search', 'archivedCount'));
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
