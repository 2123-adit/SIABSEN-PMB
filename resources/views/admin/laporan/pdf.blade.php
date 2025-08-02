<!DOCTYPE html>
<html>
<head>
    <title>Laporan Absensi</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h2 { margin: 0; }
        .info { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-center { text-align: center; }
        .badge { padding: 2px 6px; border-radius: 3px; color: white; font-size: 10px; }
        .badge-success { background-color: #28a745; }
        .badge-warning { background-color: #ffc107; color: #000; }
        .badge-info { background-color: #17a2b8; }
        .badge-danger { background-color: #dc3545; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN ABSENSI PMB</h2>
        <p>Periode: {{ $periode }}</p>
    </div>
    
    <div class="info">
        <strong>Tanggal Generate:</strong> {{ now()->format('d/m/Y H:i:s') }}<br>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Username</th>
                <th>Nama</th>
                <th>Jabatan</th>
                <th>Tanggal</th>
                <th>Jam Masuk</th>
                <th>Jam Pulang</th>
                <th>Status</th>
                <th>Terlambat</th>
            </tr>
        </thead>
        <tbody>
            @forelse($absensis as $index => $absensi)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $absensi->user->username }}</td>
                    <td>{{ $absensi->user->name }}</td>
                    <td>{{ $absensi->user->jabatan->nama_jabatan }}</td>
                    <td class="text-center">{{ $absensi->tanggal->format('d/m/Y') }}</td>
                    <td class="text-center">{{ $absensi->jam_masuk ? $absensi->jam_masuk->format('H:i') : '-' }}</td>
                    <td class="text-center">{{ $absensi->jam_pulang ? $absensi->jam_pulang->format('H:i') : '-' }}</td>
                    <td class="text-center">{{ ucfirst($absensi->status_kehadiran) }}</td>
                    <td class="text-center">{{ $absensi->menit_terlambat }} menit</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>