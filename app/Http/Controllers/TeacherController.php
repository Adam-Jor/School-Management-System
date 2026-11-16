<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeacherRequest;
use App\Models\ActivityLog;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeacherController extends Controller
{
    public function index(Request $request): View|StreamedResponse
    {
        $search = trim((string) $request->query('q', ''));
        $subjectId = $request->query('subject_id', '');
        $status = $request->query('status', '');
        $sort = $request->query('sort', 'name_asc');

        $query = Teacher::query()
            ->select('tbl_teachers.*')
            ->leftJoin('tbl_subjects', 'tbl_teachers.subject_id', '=', 'tbl_subjects.id')
            ->leftJoin('tbl_users', 'tbl_teachers.user_id', '=', 'tbl_users.id')
            ->with(['subject', 'user'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('tbl_teachers.name', 'like', "%{$search}%")
                        ->orWhere('tbl_teachers.teacher_code', 'like', "%{$search}%")
                        ->orWhere('tbl_teachers.phone', 'like', "%{$search}%")
                        ->orWhere('tbl_subjects.name', 'like', "%{$search}%");
                });
            })
            ->when($subjectId !== '', fn ($q) => $q->where('tbl_teachers.subject_id', $subjectId))
            ->when($status !== '', fn ($q) => $q->where('tbl_teachers.status', $status));

        if ($sort === 'latest') {
            $query->orderByDesc('tbl_teachers.id');
        } else {
            $query->orderBy('tbl_teachers.name', 'asc');
        }

        if ($request->query('export') === 'csv') {
            $rows = $query->get();
            ActivityLog::record('Ekspor data guru (CSV)', 'teachers', count($rows).' data guru', 'primary');

            return response()->streamDownload(function () use ($rows) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Kode Guru', 'Nama Guru', 'Mata Pelajaran', 'Email Akun', 'No. Telepon', 'Status', 'Tanggal Terdaftar']);
                foreach ($rows as $row) {
                    fputcsv($out, [
                        $row->teacher_code,
                        $row->name,
                        $row->subject?->name ?? '-',
                        $row->user?->email ?? '-',
                        $row->phone ?? '-',
                        $row->status,
                        format_date($row->created_at),
                    ]);
                }
                fclose($out);
            }, 'data-guru-'.now()->format('YmdHis').'.csv');
        }

        $teachers = $query->paginate(8)->withQueryString();
        $subjects = Subject::orderBy('name')->get();
        $stats = [
            'total' => Teacher::count(),
            'active' => Teacher::where('status', 'Aktif')->count(),
            'subjects' => Subject::count(),
            'classes' => SchoolClass::count(),
            'archived' => Teacher::onlyArchived()->count(),
        ];

        return view('teachers.index', compact(
            'teachers',
            'subjects',
            'stats',
            'search',
            'subjectId',
            'status',
            'sort'
        ));
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
            ->with('toast', 'Data guru '.$data['name'].' berhasil ditambahkan!');
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
            ->with('toast', 'Data guru '.$teacher->name.' berhasil diperbarui!');
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
            ->with('toast', 'Data guru '.$teacher->name.' telah dipindahkan ke Archive.');
    }
}
