<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Data Siswa — EDUDASH</title>
    <style>
        body { font-family: sans-serif; color: #161B33; font-size: 12px; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        p.sub { color: #6B7085; font-size: 11px; margin-top: 0; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #E3E6F6; padding: 8px 6px; text-align: left; }
        th { color: #6B7085; font-size: 11px; background: #F7F8FF; }
        td.num, th.num { text-align: right; }
    </style>
</head>
<body>
    <h1>Laporan Data Siswa — SMA Contoh Nusantara</h1>
    <p class="sub">Dicetak melalui EDUDASH pada {{ format_date(now(), true) }} · Total: {{ format_number(count($students)) }} Siswa</p>

    <table>
        <thead>
            <tr>
                <th class="num">No.</th>
                <th>Kode Siswa</th>
                <th>Nama Lengkap</th>
                <th>Kelas</th>
                <th>Email Akun</th>
                <th>Status</th>
                <th>Tgl Terdaftar</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($students as $index => $student)
                <tr>
                    <td class="num">{{ format_number($index + 1) }}</td>
                    <td>{{ $student->student_code }}</td>
                    <td><strong>{{ $student->name }}</strong></td>
                    <td>{{ $student->schoolClass?->name ?? '-' }}</td>
                    <td>{{ $student->user?->email ?? '-' }}</td>
                    <td>{{ $student->status }}</td>
                    <td>{{ format_date($student->created_at) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
