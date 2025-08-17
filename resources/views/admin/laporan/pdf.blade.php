<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Absensi</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 11px;
        }
        th, td {
            border: 1px solid black;
            padding: 4px;
            text-align: center;
            vertical-align: top;
        }
        th.rotate {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
        }
    </style>
</head>
<body>
    <h3 style="text-align: center;">Laporan Absensi</h3>
    <p><strong>Periode:</strong> {{ $periode }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Jabatan</th>
                @php
                    $dateRange = \Carbon\CarbonPeriod::create($filters['tanggal_mulai'], $filters['tanggal_selesai']);
                @endphp
                @foreach ($dateRange as $date)
                    <th>{{ $date->format('d/m') }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php
                $grouped = $absensis->groupBy('user.id');
            @endphp

            @foreach ($grouped as $userId => $records)
                @php
                    $user = $records->first()->user;
                    $recordsByDate = $records->keyBy(fn($a) => $a->tanggal->format('Y-m-d'));
                @endphp

                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->jabatan->nama_jabatan ?? '-' }}</td>

                    @foreach ($dateRange as $date)
                        @php
                            $record = $recordsByDate[$date->format('Y-m-d')] ?? null;
                        @endphp

                        <td>
                            @if ($record)
                                <strong>{{ ucfirst($record->status_kehadiran) }}</strong><br>
                                @if ($record->status_kehadiran === 'hadir')
                                    {{ $record->jam_masuk ? \Carbon\Carbon::parse($record->jam_masuk)->format('H:i') : '-' }} - 
                                    {{ $record->jam_pulang ? \Carbon\Carbon::parse($record->jam_pulang)->format('H:i') : '-' }}<br>
                                    {{ $record->menit_terlambat ?? 0 }} menit
                                @endif
                            @else
                                @php
                                    // Check if this date is a working day for this user's position
                                    $isWorkingDay = true;
                                    if ($user->jabatan && method_exists($user->jabatan, 'isWorkingDay')) {
                                        $isWorkingDay = $user->jabatan->isWorkingDay($date);
                                    } else {
                                        // Default: Senin-Jumat (not weekend)
                                        $isWorkingDay = !in_array($date->dayOfWeek, [0, 6]); // 0=Sunday, 6=Saturday
                                    }
                                @endphp
                                @if ($isWorkingDay)
                                    <strong style="color: #B71C1C;">ALFA</strong>
                                @else
                                    -
                                @endif
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
