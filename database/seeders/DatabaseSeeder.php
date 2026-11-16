<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with realistic Indonesian school records.
     */
    public function run(): void
    {
        activity()->disableLogging();

        $password = Hash::make('password');

        // 1. Admin User
        $admin = User::forceCreate([
            'name' => 'Administrator Utama',
            'email' => 'admin@edudash.sch.id',
            'username' => 'admin',
            'password' => $password,
            'phone' => '081122334455',
            'role' => 'admin',
            'status' => 'Aktif',
            'last_login_time' => now()->subMinutes(5),
            'created_at' => now()->subMonths(11),
            'updated_at' => now()->subMonths(11),
        ]);

        // 2. Subjects (tbl_subjects)
        $subjectsData = [
            ['code' => 'MP-001', 'name' => 'Matematika Wajib', 'credit_hours' => 4, 'description' => 'Aljabar, kalkulus dasar, geometri, dan statistika SMA Kurikulum Merdeka.'],
            ['code' => 'MP-002', 'name' => 'Bahasa Indonesia', 'credit_hours' => 4, 'description' => 'Literasi teks akademik, karya ilmiah, dan sastra Nusantara.'],
            ['code' => 'MP-003', 'name' => 'Fisika Terapan', 'credit_hours' => 4, 'description' => 'Mekanika klasik, termodinamika, gelombang, dan elektromagnetisme.'],
            ['code' => 'MP-004', 'name' => 'Ekonomi & Akuntansi', 'credit_hours' => 4, 'description' => 'Prinsip ekonomi makro-mikro, manajemen bisnis, dan siklus akuntansi.'],
            ['code' => 'MP-005', 'name' => 'Bahasa Inggris', 'credit_hours' => 3, 'description' => 'Academic reading, speaking, and international communication skills.'],
            ['code' => 'MP-006', 'name' => 'Biologi Molekuler', 'credit_hours' => 4, 'description' => 'Biologi sel, genetika, ekosistem, dan bioteknologi modern.'],
            ['code' => 'MP-007', 'name' => 'Kimia Analitik', 'credit_hours' => 4, 'description' => 'Struktur atom, stoikiometri, termokimia, dan larutan asam-basa.'],
            ['code' => 'MP-008', 'name' => 'Informatika & Pemrograman', 'credit_hours' => 3, 'description' => 'Algoritma, struktur data, pemrograman web, dan literasi digital.'],
        ];

        $subjects = [];
        foreach ($subjectsData as $item) {
            $subjects[$item['code']] = Subject::create($item);
        }

        // 3. Classes (tbl_classes)
        $classesData = [
            ['code' => 'CLS-10A1', 'name' => '10 IPA 1', 'capacity' => 36, 'description' => 'Kelas unggulan Sains & Teknologi tingkat X gedung A lantai 1.'],
            ['code' => 'CLS-10A2', 'name' => '10 IPA 2', 'capacity' => 36, 'description' => 'Kelas reguler Sains & Teknologi tingkat X gedung A lantai 1.'],
            ['code' => 'CLS-10S1', 'name' => '10 IPS 1', 'capacity' => 34, 'description' => 'Kelas Sosial & Humaniora tingkat X gedung B lantai 1.'],
            ['code' => 'CLS-11A1', 'name' => '11 IPA 1', 'capacity' => 36, 'description' => 'Kelas peminatan MIPA tingkat XI gedung A lantai 2.'],
            ['code' => 'CLS-11S2', 'name' => '11 IPS 2', 'capacity' => 34, 'description' => 'Kelas peminatan Ekonomi & Sosiologi tingkat XI gedung B lantai 2.'],
            ['code' => 'CLS-12A1', 'name' => '12 IPA 1', 'capacity' => 32, 'description' => 'Kelas persiapan UTBK-SNBT Sains tingkat XII gedung A lantai 3.'],
            ['code' => 'CLS-12A2', 'name' => '12 IPA 2', 'capacity' => 32, 'description' => 'Kelas reguler MIPA tingkat XII gedung A lantai 3.'],
            ['code' => 'CLS-12S1', 'name' => '12 IPS 1', 'capacity' => 32, 'description' => 'Kelas persiapan UTBK-SNBT Soshum tingkat XII gedung B lantai 3.'],
        ];

        $classes = [];
        foreach ($classesData as $item) {
            $classes[$item['name']] = SchoolClass::create($item);
        }

        // 4. Teachers (tbl_users + tbl_teachers)
        $teachersData = [
            [
                'teacher_code' => 'TCH-001',
                'name' => 'Budi Santoso, S.Pd',
                'email' => 'budi.santoso@sch.id',
                'username' => 'budi.santoso',
                'phone' => '081234567890',
                'subject_code' => 'MP-001',
                'status' => 'Aktif',
                'notes' => 'Wali Kelas 10 IPA 1 · Pembina Olimpiade Matematika Provinsi.',
            ],
            [
                'teacher_code' => 'TCH-002',
                'name' => 'Siti Aminah, M.Pd',
                'email' => 'siti.aminah@sch.id',
                'username' => 'siti.aminah',
                'phone' => '081398765432',
                'subject_code' => 'MP-004',
                'status' => 'Aktif',
                'notes' => 'Wali Kelas 11 IPS 2 · Koordinator Kurikulum Bidang Sosial.',
            ],
            [
                'teacher_code' => 'TCH-003',
                'name' => 'Hendra Wijaya, S.Kom',
                'email' => 'hendra.wijaya@sch.id',
                'username' => 'hendra.wijaya',
                'phone' => '085723456789',
                'subject_code' => 'MP-008',
                'status' => 'Aktif',
                'notes' => 'Wali Kelas 12 IPA 1 · Kepala Laboratorium Komputer & Jaringan.',
            ],
            [
                'teacher_code' => 'TCH-004',
                'name' => 'Dewi Kartika, S.Pd',
                'email' => 'dewi.kartika@sch.id',
                'username' => 'dewi.kartika',
                'phone' => '082187654321',
                'subject_code' => 'MP-002',
                'status' => 'Aktif',
                'notes' => 'Wali Kelas 10 IPS 1 · Pembina Jurnalistik dan Sastra Sekolah.',
            ],
            [
                'teacher_code' => 'TCH-005',
                'name' => 'Drs. Ahmad Fauzi',
                'email' => 'ahmad.fauzi@sch.id',
                'username' => 'ahmad.fauzi',
                'phone' => '081122339988',
                'subject_code' => 'MP-003',
                'status' => 'Aktif',
                'notes' => 'Pengajar Senior Fisika Terapan · Kepala Laboratorium IPA.',
            ],
            [
                'teacher_code' => 'TCH-006',
                'name' => 'Rina Wulandari, M.Si',
                'email' => 'rina.wulandari@sch.id',
                'username' => 'rina.wulandari',
                'phone' => '081567891234',
                'subject_code' => 'MP-006',
                'status' => 'Aktif',
                'notes' => 'Wali Kelas 11 IPA 1 · Pembina Karya Ilmiah Remaja (KIR).',
            ],
            [
                'teacher_code' => 'TCH-007',
                'name' => 'Rizky Pratama, S.Si',
                'email' => 'rizky.pratama@sch.id',
                'username' => 'rizky.pratama',
                'phone' => '081987651234',
                'subject_code' => 'MP-007',
                'status' => 'Cuti',
                'notes' => 'Sedang tugas belajar pelatihan laboratorium nasional.',
            ],
            [
                'teacher_code' => 'TCH-008',
                'name' => 'Nadia Larasati, M.Hum',
                'email' => 'nadia.larasati@sch.id',
                'username' => 'nadia.larasati',
                'phone' => '081299887766',
                'subject_code' => 'MP-005',
                'status' => 'Aktif',
                'notes' => 'Pembina English Debate Club & TOEFL Preparation.',
            ],
        ];

        $teacherClassMap = [
            'TCH-001' => ['10 IPA 1', '10 IPA 2', '11 IPA 1'],
            'TCH-002' => ['11 IPS 2', '10 IPS 1', '12 IPS 1'],
            'TCH-003' => ['12 IPA 1', '12 IPA 2'],
            'TCH-004' => ['10 IPS 1', '10 IPA 1'],
            'TCH-005' => ['10 IPA 1', '11 IPA 1', '12 IPA 1'],
            'TCH-006' => ['11 IPA 1', '12 IPA 2'],
            'TCH-007' => ['10 IPA 2'],
            'TCH-008' => ['12 IPA 2', '12 IPS 1'],
        ];

        $teacherMonthOffsets = [10, 8, 6, 5, 4, 2, 1, 0];
        foreach ($teachersData as $idx => $t) {
            $createdAt = now()->subMonths($teacherMonthOffsets[$idx] ?? 0)->startOfMonth()->addDays(5 + $idx);

            $user = User::forceCreate([
                'name' => $t['name'],
                'email' => $t['email'],
                'username' => $t['username'],
                'password' => $password,
                'phone' => $t['phone'],
                'role' => 'teacher',
                'status' => $t['status'],
                'last_login_time' => now()->subHours(rand(1, 24)),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $teacher = Teacher::forceCreate([
                'user_id' => $user->id,
                'subject_id' => $subjects[$t['subject_code']]->id,
                'teacher_code' => $t['teacher_code'],
                'name' => $t['name'],
                'phone' => $t['phone'],
                'status' => $t['status'],
                'notes' => $t['notes'],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $classIds = collect($teacherClassMap[$t['teacher_code']] ?? [])
                ->map(fn ($name) => $classes[$name]->id ?? null)
                ->filter()
                ->values()
                ->all();
            if (! empty($classIds)) {
                $teacher->schoolClasses()->sync($classIds);
            }
        }

        // 5. Students (tbl_users + tbl_students)
        $studentsData = [
            ['code' => 'STD-001', 'name' => 'Alya Putri Ramadhani', 'email' => 'alya.putri@sch.id', 'username' => 'alya.putri', 'class' => '10 IPA 1', 'status' => 'Aktif', 'phone' => '081234000001', 'address' => 'Jl. Dago Asri No. 14, Coblong, Kota Bandung'],
            ['code' => 'STD-002', 'name' => 'Bagas Pratama Wijaya', 'email' => 'bagas.pratama@sch.id', 'username' => 'bagas.pratama', 'class' => '11 IPS 2', 'status' => 'Aktif', 'phone' => '081234000002', 'address' => 'Jl. Buah Batu No. 88, Lengkong, Kota Bandung'],
            ['code' => 'STD-003', 'name' => 'Citra Lestari Kusuma', 'email' => 'citra.lestari@sch.id', 'username' => 'citra.lestari', 'class' => '12 IPA 1', 'status' => 'Pindah', 'phone' => '081234000003', 'address' => 'Jl. Setiabudi No. 112, Sukasari, Kota Bandung'],
            ['code' => 'STD-004', 'name' => 'Dimas Arya Nugraha', 'email' => 'dimas.arya@sch.id', 'username' => 'dimas.arya', 'class' => '10 IPA 1', 'status' => 'Aktif', 'phone' => '081234000004', 'address' => 'Jl. Cihampelas No. 45, Bandung Wetan, Kota Bandung'],
            ['code' => 'STD-005', 'name' => 'Eka Nabila Safitri', 'email' => 'eka.nabila@sch.id', 'username' => 'eka.nabila', 'class' => '10 IPS 1', 'status' => 'Menunggu', 'phone' => '081234000005', 'address' => 'Jl. Antapani Raya No. 23, Antapani, Kota Bandung'],
            ['code' => 'STD-006', 'name' => 'Farhan Rizky Pratama', 'email' => 'farhan.rizky@sch.id', 'username' => 'farhan.rizky', 'class' => '12 IPA 2', 'status' => 'Aktif', 'phone' => '081234000006', 'address' => 'Jl. Dipatiukur No. 67, Coblong, Kota Bandung'],
            ['code' => 'STD-007', 'name' => 'Gita Permata Sari', 'email' => 'gita.permata@sch.id', 'username' => 'gita.permata', 'class' => '11 IPA 1', 'status' => 'Aktif', 'phone' => '081234000007', 'address' => 'Jl. Pasteur No. 19, Sukajadi, Kota Bandung'],
            ['code' => 'STD-008', 'name' => 'Hafiz Maulana Akbar', 'email' => 'hafiz.maulana@sch.id', 'username' => 'hafiz.maulana', 'class' => '10 IPA 2', 'status' => 'Aktif', 'phone' => '081234000008', 'address' => 'Jl. Soekarno Hatta No. 310, Buahbatu, Kota Bandung'],
            ['code' => 'STD-009', 'name' => 'Indah Cahyani Putri', 'email' => 'indah.cahyani@sch.id', 'username' => 'indah.cahyani', 'class' => '12 IPS 1', 'status' => 'Aktif', 'phone' => '081234000009', 'address' => 'Jl. Gatot Subroto No. 54, Lengkong, Kota Bandung'],
            ['code' => 'STD-010', 'name' => 'Jihan Aulia Rahma', 'email' => 'jihan.aulia@sch.id', 'username' => 'jihan.aulia', 'class' => '11 IPA 1', 'status' => 'Aktif', 'phone' => '081234000010', 'address' => 'Jl. Surya Sumantri No. 72, Sukajadi, Kota Bandung'],
            ['code' => 'STD-011', 'name' => 'Kevin Sanjaya Putra', 'email' => 'kevin.sanjaya@sch.id', 'username' => 'kevin.sanjaya', 'class' => '10 IPA 1', 'status' => 'Aktif', 'phone' => '081234000011', 'address' => 'Jl. Riau No. 102, Bandung Wetan, Kota Bandung'],
            ['code' => 'STD-012', 'name' => 'Lutfi Hakim Ramadhan', 'email' => 'lutfi.hakim@sch.id', 'username' => 'lutfi.hakim', 'class' => '12 IPA 1', 'status' => 'Aktif', 'phone' => '081234000012', 'address' => 'Jl. Ciumbuleuit No. 39, Cidadap, Kota Bandung'],
        ];

        $studentMonthOffsets = [11, 10, 9, 8, 6, 5, 4, 3, 2, 1, 0, 0];
        $createdStudents = [];
        foreach ($studentsData as $idx => $s) {
            $createdAt = now()->subMonths($studentMonthOffsets[$idx] ?? 0)->startOfMonth()->addDays(min(14, 2 + $idx));

            $user = User::forceCreate([
                'name' => $s['name'],
                'email' => $s['email'],
                'username' => $s['username'],
                'password' => $password,
                'phone' => $s['phone'],
                'role' => 'student',
                'status' => $s['status'] === 'Pindah' ? 'Nonaktif' : 'Aktif',
                'last_login_time' => now()->subHours(rand(2, 72)),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $student = Student::forceCreate([
                'user_id' => $user->id,
                'class_id' => $classes[$s['class']]->id,
                'student_code' => $s['code'],
                'name' => $s['name'],
                'phone' => $s['phone'],
                'address' => $s['address'],
                'status' => $s['status'],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $createdStudents[] = $student;
        }

        // 6. Sample Attendances & Grades (Fitur Tambahan PRD 6 & 7.2)
        foreach (array_slice($createdStudents, 0, 6) as $student) {
            Attendance::create([
                'student_id' => $student->id,
                'class_id' => $student->class_id,
                'date' => now()->toDateString(),
                'status' => 'Hadir',
                'notes' => 'Hadir tepat waktu pukul 06:50 WIB',
            ]);

            Grade::create([
                'student_id' => $student->id,
                'subject_id' => $subjects['MP-001']->id,
                'semester' => 'Ganjil 2026/2027',
                'score' => rand(84, 96),
                'predicate' => 'A',
            ]);
        }

        // 7. Activity Logs (design.md Section 4.8)
        activity()->enableLogging();

        $logs = [
            ['action' => 'Data siswa diperbarui', 'module' => 'students', 'target_label' => 'Admin · Alya Putri Ramadhani (STD-001)', 'badge_color' => 'primary'],
            ['action' => 'Kelas baru ditambahkan', 'module' => 'classes', 'target_label' => 'Admin · Kelas 10 IPA 1 (CLS-10A1)', 'badge_color' => 'success'],
            ['action' => 'Status siswa diubah ke Pindah', 'module' => 'students', 'target_label' => 'Admin · Citra Lestari Kusuma (STD-003)', 'badge_color' => 'error'],
            ['action' => 'Jadwal & pengajar mapel diperbarui', 'module' => 'teachers', 'target_label' => 'Admin · Budi Santoso, S.Pd (MP-001)', 'badge_color' => 'primary'],
            ['action' => 'Ekspor laporan PDF siswa', 'module' => 'students', 'target_label' => 'Guru · Budi Santoso, S.Pd', 'badge_color' => 'muted'],
        ];

        foreach ($logs as $index => $log) {
            $entry = activity($log['module'])
                ->causedBy($admin)
                ->withProperties([
                    'target_label' => $log['target_label'],
                    'badge_color' => $log['badge_color'],
                ])
                ->log($log['action']);

            $entry->timestamps = false;
            $entry->created_at = now()->subMinutes(($index + 1) * 14);
            $entry->save();
        }
    }
}
