<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Data Guru — EDUDASH</title>
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
    <h1>Laporan Data Guru — SMA Contoh Nusantara</h1>
    <p class="sub">Dicetak melalui EDUDASH pada {{ format_date(now(), true) }} · Total: {{ format_number(count($teachers)) }} Guru</p>

    <table>
        <thead>
            <tr>
                <th class="num">No.</th>
                <th>Kode Guru</th>
                <th>Nama Lengkap & Gelar</th>
                <th>Mata Pelajaran</th>
                <th>No. Telepon</th>
                <th>Status</th>
                <th>Tgl Terdaftar</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($teachers as $index => $teacher)
                <tr>
                    <td class="num">{{ format_number($index + 1) }}</td>
                    <td>{{ $teacher->teacher_code }}</td>
                    <td><strong>{{ $teacher->name }}</strong></td>
                    <td>{{ $teacher->subject?->name ?? '-' }}</td>
                    <td>{{ $teacher->phone ?? '-' }}</td>
                    <td>{{ $teacher->status }}</td>
                    <td>{{ format_date($teacher->created_at) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
