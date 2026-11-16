<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rapor Akademik Siswa — {{ $student->name }}</title>
    <style>
        body { font-family: sans-serif; color: #161B33; font-size: 12px; line-height: 1.5; }
        .header { border-bottom: 2px solid #161B33; padding-bottom: 10px; margin-bottom: 16px; }
        .header h1 { font-size: 18px; margin: 0; }
        .header p { margin: 2px 0 0; color: #6B7085; font-size: 11px; }
        .meta { width: 100%; margin-bottom: 18px; }
        .meta td { padding: 3px 6px; font-size: 12px; }
        table.grades { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.grades th, table.grades td { border: 1px solid #E3E6F6; padding: 8px; }
        table.grades th { background: #F7F8FF; color: #6B7085; font-size: 11px; text-align: left; }
        .num { text-align: right; }
        .center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>RAPOR CAPAIAN HASIL BELAJAR SISWA — SMA CONTOH NUSANTARA</h1>
        <p>Sistem Informasi Akademik Terpadu EDUDASH · Tahun Ajaran 2026/2027 · Dicetak: {{ format_date(now(), true) }}</p>
    </div>

    <table class="meta">
        <tr>
            <td width="18%"><strong>Nama Siswa</strong></td>
            <td width="35%">: {{ $student->name }}</td>
            <td width="18%"><strong>Kelas</strong></td>
            <td>: {{ $student->schoolClass?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Kode Siswa</strong></td>
            <td>: {{ $student->student_code }}</td>
            <td><strong>Status</strong></td>
            <td>: {{ $student->status }}</td>
        </tr>
    </table>

    <h3>A. Nilai Capaian Mata Pelajaran</h3>
    <table class="grades">
        <thead>
            <tr>
                <th class="num" width="8%">No.</th>
                <th width="16%">Kode Mapel</th>
                <th>Mata Pelajaran</th>
                <th width="20%">Semester</th>
                <th class="num" width="14%">Nilai Akhir</th>
                <th class="center" width="12%">Predikat</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($grades as $idx => $g)
                <tr>
                    <td class="num">{{ format_number($idx + 1) }}</td>
                    <td>{{ $g->subject?->code }}</td>
                    <td><strong>{{ $g->subject?->name }}</strong></td>
                    <td>{{ $g->semester }}</td>
                    <td class="num"><strong>{{ format_number($g->score) }}</strong></td>
                    <td class="center"><strong>{{ $g->predicate }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="center">Belum ada nilai mata pelajaran yang diinput.</td>
                </tr>
            @endforelse
            <tr>
                <td colspan="4" class="num"><strong>Rata-rata Nilai Akademik</strong></td>
                <td class="num"><strong>{{ format_number($averageScore, 2) }}</strong></td>
                <td class="center"><strong>{{ $averageScore >= 85 ? 'A' : ($averageScore >= 75 ? 'B' : 'C') }}</strong></td>
            </tr>
        </tbody>
    </table>

    <h3>B. Rekapitulasi Kehadiran (Absensi)</h3>
    <table class="grades" style="width: 60%;">
        <thead>
            <tr>
                <th>Status Kehadiran</th>
                <th class="num">Jumlah Hari</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>Hadir</td><td class="num">{{ format_number($attendanceSummary['Hadir']) }} Hari</td></tr>
            <tr><td>Izin</td><td class="num">{{ format_number($attendanceSummary['Izin']) }} Hari</td></tr>
            <tr><td>Sakit</td><td class="num">{{ format_number($attendanceSummary['Sakit']) }} Hari</td></tr>
            <tr><td>Tanpa Keterangan (Alpa)</td><td class="num">{{ format_number($attendanceSummary['Alpa']) }} Hari</td></tr>
        </tbody>
    </table>
</body>
</html>
