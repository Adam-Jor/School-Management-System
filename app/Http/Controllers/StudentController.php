<?php

namespace App\Http\Controllers;

use App\Exports\StudentsExport;
use App\Http\Requests\ExcelImportRequest;
use App\Http\Requests\StudentRequest;
use App\Imports\StudentsImport;
use App\Models\ActivityLog;
use App\Models\SchoolClass;
use App\Models\Student;
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

class StudentController extends Controller
{
    public function index(Request $request): View|BinaryFileResponse|Response
    {
        $user = $request->user();
        $isStudent = $user?->isStudent() ?? false;
        $onlyUserId = $isStudent ? $user->id : null;

        $search = trim((string) $request->query('q', ''));
        $classId = $isStudent ? '' : (string) $request->query('class_id', '');
        $status = $isStudent ? '' : (string) $request->query('status', '');
        $exportType = (string) $request->query('export', '');

        if (in_array($exportType, ['xlsx', 'csv'], true)) {
            $format = $exportType === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;
            $extension = $exportType === 'csv' ? 'csv' : 'xlsx';
            ActivityLog::record("Ekspor data siswa ({$extension})", 'students', 'Ekspor Excel', 'primary');

            return Excel::download(
                new StudentsExport($search, $classId, $status, $onlyUserId),
                'data-siswa-'.now()->format('YmdHis').'.'.$extension,
                $format
            );
        }

        if ($exportType === 'pdf') {
            $rows = (new StudentsExport($search, $classId, $status, $onlyUserId))->query()->get();
            ActivityLog::record('Ekspor laporan PDF siswa', 'students', count($rows).' data siswa', 'muted');

            $pdf = Pdf::loadView('students.pdf', ['students' => $rows]);

            return $pdf->download('laporan-data-siswa-'.now()->format('YmdHis').'.pdf');
        }

        $archivedCount = $user?->isAdmin() ? Student::onlyArchived()->count() : 0;

        return view('students.index', compact('archivedCount'));
    }

    public function create(): View
    {
        $classes = SchoolClass::orderBy('name')->get();
        $users = User::where('role', 'student')->orderBy('name')->get();
        $nextNumber = Student::withArchived()->count() + 1;
        $nextCode = 'STD-'.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);

        return view('students.add', compact('classes', 'users', 'nextCode'));
    }

    public function store(StudentRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, &$data) {
            if (empty($data['user_id'])) {
                $usernameBase = Str::slug($data['name'], '.');
                $email = $usernameBase.'.'.strtolower($data['student_code']).'@sch.id';
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $email,
                    'username' => $usernameBase.'.'.strtolower($data['student_code']),
                    'password' => Hash::make('password'),
                    'phone' => $data['phone'] ?? null,
                    'role' => 'student',
                    'status' => $data['status'] === 'Pindah' ? 'Nonaktif' : 'Aktif',
                ]);
                $data['user_id'] = $user->id;
            }

            if ($request->hasFile('photo')) {
                $data['img_url'] = upload_image($request->file('photo'), 'students');
            }

            $student = Student::create($data);

            ActivityLog::record(
                'Siswa baru ditambahkan',
                'students',
                $request->user()->name.' · '.$student->name.' ('.$student->student_code.')',
                'success'
            );
        });

        return redirect()->route('students.index')
            ->with('toast', 'Data siswa '.$data['name'].' ('.$data['student_code'].') berhasil disimpan');
    }

    public function edit(Student $student): View
    {
        $classes = SchoolClass::orderBy('name')->get();
        $users = User::where('role', 'student')->orderBy('name')->get();

        return view('students.edit', compact('student', 'classes', 'users'));
    }

    public function update(StudentRequest $request, Student $student): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['img_url'] = upload_image($request->file('photo'), 'students');
        }

        $student->update($data);

        ActivityLog::record(
            'Data siswa diperbarui',
            'students',
            $request->user()->name.' · '.$student->name.' ('.$student->student_code.')',
            'primary'
        );

        return redirect()->route('students.index')
            ->with('toast', 'Data siswa '.$student->name.' ('.$student->student_code.') berhasil diperbarui');
    }

    public function archive(Request $request, Student $student): RedirectResponse
    {
        if (! $request->user()?->isAdmin()) {
            abort(403);
        }

        $student->archive();

        ActivityLog::record(
            'Data siswa di-archive (soft delete)',
            'students',
            $request->user()->name.' · '.$student->name.' ('.$student->student_code.')',
            'error'
        );

        return redirect()->route('students.index')
            ->with('toast', 'Data siswa '.$student->name.' telah dipindahkan ke Archive');
    }

    public function import(ExcelImportRequest $request): RedirectResponse
    {
        $request->validated();

        $uploadedFile = $request->file('excel_file') ?? $request->file('csv_file');
        $importer = new StudentsImport;
        Excel::import($importer, $uploadedFile);

        ActivityLog::record('Import Excel/CSV data siswa', 'students', "{$importer->imported} siswa berhasil diimpor", 'success');

        return redirect()->route('students.index')
            ->with('toast', "Berhasil mengimpor {$importer->imported} data siswa baru");
    }
}
