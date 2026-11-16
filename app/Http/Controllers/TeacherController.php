<?php

namespace App\Http\Controllers;

use App\Exports\TeachersExport;
use App\Http\Requests\TeacherRequest;
use App\Imports\TeachersImport;
use App\Models\ActivityLog;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TeacherController extends Controller
{
    public function index(Request $request): View|BinaryFileResponse|Response
    {
        $search = trim((string) $request->query('q', ''));
        $subjectId = (string) $request->query('subject_id', '');
        $status = (string) $request->query('status', '');
        $exportType = (string) $request->query('export', '');

        if (in_array($exportType, ['xlsx', 'csv'], true)) {
            $format = $exportType === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;
            $extension = $exportType === 'csv' ? 'csv' : 'xlsx';
            ActivityLog::record("Ekspor data guru ({$extension})", 'teachers', 'Maatwebsite Excel Export', 'primary');

            return Excel::download(
                new TeachersExport($search, $subjectId, $status),
                'data-guru-'.now()->format('YmdHis').'.'.$extension,
                $format
            );
        }

        if ($exportType === 'pdf') {
            $rows = (new TeachersExport($search, $subjectId, $status))->query()->get();
            ActivityLog::record('Ekspor laporan PDF guru', 'teachers', count($rows).' data guru', 'muted');

            $pdf = Pdf::loadView('teachers.pdf', ['teachers' => $rows]);

            return $pdf->download('laporan-data-guru-'.now()->format('YmdHis').'.pdf');
        }

        $stats = [
            'total' => Teacher::count(),
            'active' => Teacher::where('status', 'Aktif')->count(),
            'subjects' => Subject::count(),
            'classes' => SchoolClass::count(),
            'archived' => Teacher::onlyArchived()->count(),
        ];

        return view('teachers.index', compact('stats'));
    }

    public function create(): View
    {
        $subjects = Subject::orderBy('name')->get();
        $users = User::where('role', 'teacher')->orderBy('name')->get();
        $nextNumber = Teacher::withArchived()->count() + 1;
        $nextCode = 'TCH-'.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);

        return view('teachers.add', compact('subjects', 'users', 'nextCode'));
    }

    public function store(TeacherRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, &$data) {
            if (empty($data['user_id'])) {
                $cleanName = preg_replace('/[,.].*$/', '', $data['name']) ?: $data['name'];
                $usernameBase = Str::slug($cleanName, '.');
                $email = $usernameBase.'.'.strtolower($data['teacher_code']).'@sch.id';
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $email,
                    'username' => $usernameBase.'.'.strtolower($data['teacher_code']),
                    'password' => Hash::make('password'),
                    'phone' => $data['phone'] ?? null,
                    'role' => 'teacher',
                    'status' => $data['status'] === 'Pindah' ? 'Nonaktif' : 'Aktif',
                ]);
                $data['user_id'] = $user->id;
            }

            if ($request->hasFile('photo')) {
                $data['img_url'] = upload_image($request->file('photo'), 'teachers');
            }

            $teacher = Teacher::create($data);

            ActivityLog::record(
                'Data guru baru ditambahkan',
                'teachers',
                $request->user()->name.' · '.$teacher->name.' ('.$teacher->teacher_code.')',
                'success'
            );
        });

        return redirect()->route('teachers.index')
            ->with('toast', 'Data guru '.$data['name'].' berhasil ditambahkan');
    }

    public function edit(Teacher $teacher): View
    {
        $teacher->load(['subject', 'user']);
        $subjects = Subject::orderBy('name')->get();
        $users = User::where('role', 'teacher')->orderBy('name')->get();

        return view('teachers.edit', compact('teacher', 'subjects', 'users'));
    }

    public function update(TeacherRequest $request, Teacher $teacher): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['img_url'] = upload_image($request->file('photo'), 'teachers');
        }

        $teacher->update($data);

        ActivityLog::record(
            'Data guru diperbarui',
            'teachers',
            $request->user()->name.' · '.$teacher->name.' ('.$teacher->teacher_code.')',
            'primary'
        );

        return redirect()->route('teachers.index')
            ->with('toast', 'Data guru '.$teacher->name.' berhasil diperbarui');
    }

    public function archive(Request $request, Teacher $teacher): RedirectResponse
    {
        if (! $request->user()?->isAdmin()) {
            abort(403);
        }

        $teacher->archive();

        ActivityLog::record(
            'Data guru di-archive',
            'teachers',
            $request->user()->name.' · '.$teacher->name.' ('.$teacher->teacher_code.')',
            'error'
        );

        return redirect()->route('teachers.index')
            ->with('toast', 'Data guru '.$teacher->name.' telah dipindahkan ke Archive');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'excel_file' => ['required_without:csv_file', 'nullable', 'file', 'mimes:xlsx,xls,csv,txt', 'max:4096'],
            'csv_file' => ['nullable', 'file', 'mimes:xlsx,xls,csv,txt', 'max:4096'],
        ]);

        $uploadedFile = $request->file('excel_file') ?? $request->file('csv_file');
        $importer = new TeachersImport;
        Excel::import($importer, $uploadedFile);

        ActivityLog::record('Import Excel/CSV data guru', 'teachers', "{$importer->imported} guru berhasil diimpor", 'success');

        return redirect()->route('teachers.index')
            ->with('toast', "Berhasil mengimpor {$importer->imported} data guru baru");
    }
}
