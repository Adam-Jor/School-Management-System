<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentRequest;
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
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentController extends Controller
{
    public function index(Request $request): View|StreamedResponse|Response
    {
        $search = trim((string) $request->query('q', ''));
        $classId = $request->query('class_id', '');
        $status = $request->query('status', '');
        $sort = $request->query('sort', 'latest');

        // Menggunakan JOIN ke tbl_classes dan tbl_users sesuai PRD 4.3 & 6
        $query = Student::query()
            ->select('tbl_students.*')
            ->leftJoin('tbl_classes', 'tbl_students.class_id', '=', 'tbl_classes.id')
            ->leftJoin('tbl_users', 'tbl_students.user_id', '=', 'tbl_users.id')
            ->with(['schoolClass', 'user'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('tbl_students.name', 'like', "%{$search}%")
                        ->orWhere('tbl_students.student_code', 'like', "%{$search}%")
                        ->orWhere('tbl_users.email', 'like', "%{$search}%")
                        ->orWhere('tbl_classes.name', 'like', "%{$search}%");
                });
            })
            ->when($classId !== '', fn ($q) => $q->where('tbl_students.class_id', $classId))
            ->when($status !== '', fn ($q) => $q->where('tbl_students.status', $status));

        if ($sort === 'name_asc') {
            $query->orderBy('tbl_students.name', 'asc');
        } elseif ($sort === 'code_asc') {
            $query->orderBy('tbl_students.student_code', 'asc');
        } else {
            $query->orderByDesc('tbl_students.id');
        }

        if ($request->query('export') === 'csv') {
            $rows = $query->get();
            ActivityLog::record('Ekspor data siswa (CSV/Excel)', 'students', count($rows).' baris data siswa', 'primary');

            return response()->streamDownload(function () use ($rows) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Kode Siswa', 'Nama Lengkap', 'Kelas', 'Email Akun', 'No. Telepon', 'Status', 'Tanggal Terdaftar']);
                foreach ($rows as $row) {
                    fputcsv($out, [
                        $row->student_code,
                        $row->name,
                        $row->schoolClass?->name ?? '-',
                        $row->user?->email ?? '-',
                        $row->phone ?? '-',
                        $row->status,
                        format_date($row->created_at),
                    ]);
                }
                fclose($out);
            }, 'data-siswa-'.now()->format('YmdHis').'.csv');
        }

        if ($request->query('export') === 'pdf') {
            $rows = $query->get();
            ActivityLog::record('Ekspor laporan PDF siswa', 'students', count($rows).' data siswa', 'muted');

            $pdf = Pdf::loadView('students.pdf', ['students' => $rows]);

            return $pdf->download('laporan-data-siswa-'.now()->format('YmdHis').'.pdf');
        }

        $students = $query->paginate(8)->withQueryString();
        $classes = SchoolClass::orderBy('name')->get();
        $archivedCount = Student::onlyArchived()->count();

        return view('students.index', compact(
            'students',
            'classes',
            'search',
            'classId',
            'status',
            'sort',
            'archivedCount'
        ));
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
            ->with('toast', 'Data siswa '.$data['name'].' ('.$data['student_code'].') berhasil disimpan!');
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
            ->with('toast', 'Data siswa '.$student->name.' ('.$student->student_code.') berhasil diperbarui!');
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
            ->with('toast', 'Data siswa '.$student->name.' telah dipindahkan ke Archive.');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ], [
            'csv_file.required' => 'Pilih file CSV untuk diimpor.',
            'csv_file.mimes' => 'File harus berformat CSV (.csv).',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        $defaultClass = SchoolClass::first();
        $imported = 0;
        $rowNum = 0;

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $rowNum++;
            if ($rowNum === 1 && stripos($row[0] ?? '', 'kode') !== false) {
                continue;
            }
            $code = trim($row[0] ?? '');
            $name = trim($row[1] ?? '');
            if ($code === '' || $name === '') {
                continue;
            }
            if (Student::withArchived()->where('student_code', $code)->exists()) {
                continue;
            }

            Student::create([
                'student_code' => $code,
                'name' => $name,
                'class_id' => $defaultClass?->id,
                'status' => in_array($row[2] ?? '', ['Aktif', 'Pindah', 'Menunggu'], true) ? $row[2] : 'Aktif',
            ]);
            $imported++;
        }
        fclose($handle);

        ActivityLog::record('Import CSV data siswa', 'students', "{$imported} siswa berhasil diimpor", 'success');

        return redirect()->route('students.index')
            ->with('toast', "Berhasil mengimpor {$imported} data siswa baru.");
    }
}
