<!DOCTYPE html>
<html>
<head>
    <title>Slip Absensi - {{ $user->name }}</title>
    <meta charset="utf-8">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px; 
            margin: 0;
            padding: 20px;
            line-height: 1.4;
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px; 
            border-bottom: 3px solid #667eea; 
            padding-bottom: 15px; 
        }
        .header h2 { 
            margin: 0; 
            color: #2c3e50;
            font-size: 18px;
            font-weight: bold;
        }
        .header .subtitle {
            color: #667eea;
            font-size: 14px;
            margin: 5px 0;
        }
        .info { 
            margin-bottom: 25px; 
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .info table { 
            width: 100%; 
            border-collapse: collapse;
        }
        .info td { 
            padding: 8px 5px; 
            vertical-align: top;
        }
        .info .label {
            font-weight: bold;
            color: #2c3e50;
            width: 120px;
        }
        .info .colon {
            width: 15px;
            text-align: center;
        }
        .statistik { 
            margin: 25px 0; 
        }
        .statistik h3 {
            color: #2c3e50;
            border-bottom: 2px solid #667eea;
            padding-bottom: 8px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .statistik table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px;
        }
        .statistik th, .statistik td { 
            border: 1px solid #ddd; 
            padding: 10px 8px; 
            text-align: center; 
            font-size: 11px;
        }
        .statistik th { 
            background-color: #667eea; 
            color: white;
            font-weight: bold;
        }
        .statistik .stat-hadir { background-color: #d4edda; color: #155724; }
        .statistik .stat-izin { background-color: #fff3cd; color: #856404; }
        .statistik .stat-sakit { background-color: #d1ecf1; color: #0c5460; }
        .statistik .stat-alfa { background-color: #f8d7da; color: #721c24; }
        .statistik .stat-terlambat { background-color: #ffeaa7; color: #d63031; }
        
        .detail-section {
            margin: 25px 0;
        }
        .detail-section h3 {
            color: #2c3e50;
            border-bottom: 2px solid #667eea;
            padding-bottom: 8px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .detail-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
        }
        .detail-table th, .detail-table td { 
            border: 1px solid #ddd; 
            padding: 8px 6px; 
            text-align: center; 
            font-size: 10px;
        }
        .detail-table th { 
            background-color: #667eea; 
            color: white;
            font-weight: bold;
        }
        .detail-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .detail-table tr:hover {
            background-color: #e3f2fd;
        }
        .status-hadir { color: #28a745; font-weight: bold; }
        .status-izin { color: #ffc107; font-weight: bold; }
        .status-sakit { color: #17a2b8; font-weight: bold; }
        .status-alfa { color: #dc3545; font-weight: bold; }
        
        .signature {
            margin-top: 50px;
            text-align: right;
            page-break-inside: avoid;
        }
        .signature table {
            margin-left: auto;
            border-collapse: collapse;
        }
        .signature td {
            padding: 5px 15px;
            text-align: center;
            vertical-align: top;
        }
        .signature .sign-box {
            border: 1px solid #ddd;
            width: 150px;
            height: 80px;
            margin: 10px 0;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
        }
        
        @media print {
            body { margin: 0; padding: 15px; }
            .header { margin-bottom: 20px; }
            .page-break { page-break-before: always; }
        }
        
        .no-data {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>SLIP ABSENSI KARYAWAN</h2>
        <div class="subtitle">SISTEM INFORMASI ABSENSI PMB</div>
        <div class="subtitle">Periode: {{ $periode }}</div>
    </div>
    
    <div class="info">
        <table>
            <tr>
                <td class="label">User ID</td>
                <td class="colon">:</td>
                <td>{{ $user->user_id }}</td>
                <td class="label">Jabatan</td>
                <td class="colon">:</td>
                <td>{{ $user->jabatan->nama_jabatan }}</td>
            </tr>
            <tr>
                <td class="label">Nama Lengkap</td>
                <td class="colon">:</td>
                <td>{{ $user->name }}</td>
                <td class="label">Jam Kerja</td>
                <td class="colon">:</td>
                <td>{{ $user->jam_masuk->format('H:i') }} - {{ $user->jam_pulang->format('H:i') }}</td>
            </tr>
        </table>
    </div>

    <div class="statistik">
        <h3>📊 RINGKASAN KEHADIRAN BULAN {{ strtoupper($periode) }}</h3>
        <table>
            <thead>
                <tr>
                    <th>Total Hadir</th>
                    <th>Total Izin</th>
                    <th>Total Sakit</th>
                    <th>Total Alfa</th>
                    <th>Total Terlambat</th>
                    <th>Persentase Kehadiran</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="stat-hadir">{{ $statistik['total_hadir'] }}</td>
                    <td class="stat-izin">{{ $statistik['total_izin'] }}</td>
                    <td class="stat-sakit">{{ $statistik['total_sakit'] }}</td>
                    <td class="stat-alfa">{{ $statistik['total_alfa'] }}</td>
                    <td class="stat-terlambat">{{ $statistik['total_terlambat'] }}</td>
                    <td style="font-weight: bold;">
                        @php
                            $totalKehadiran = $statistik['total_hadir'] + $statistik['total_izin'] + $statistik['total_sakit'] + $statistik['total_alfa'];
                            $persentase = $totalKehadiran > 0 ? round(($statistik['total_hadir'] / $totalKehadiran) * 100, 1) : 0;
                        @endphp
                        {{ $persentase }}%
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="detail-section">
        <h3>📅 DETAIL ABSENSI HARIAN</h3>
        
        @if($absensis->count() > 0)
            <table class="detail-table">
                <thead>
                    <tr>
                        <th style="width: 8%;">No</th>
                        <th style="width: 15%;">Tanggal</th>
                        <th style="width: 12%;">Hari</th>
                        <th style="width: 12%;">Jam Masuk</th>
                        <th style="width: 12%;">Jam Pulang</th>
                        <th style="width: 15%;">Status Kehadiran</th>
                        <th style="width: 12%;">Terlambat</th>
                        <th style="width: 14%;">Total Jam Kerja</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($absensis as $index => $absensi)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $absensi->tanggal->format('d/m/Y') }}</td>
                            <td>{{ $absensi->tanggal->format('D') }}</td>
                            <td>{{ $absensi->jam_masuk ? $absensi->jam_masuk->format('H:i') : '-' }}</td>
                            <td>{{ $absensi->jam_pulang ? $absensi->jam_pulang->format('H:i') : '-' }}</td>
                            <td>
                                <span class="status-{{ $absensi->status_kehadiran }}">
                                    {{ ucfirst($absensi->status_kehadiran) }}
                                </span>
                                @if($absensi->status_masuk == 'terlambat')
                                    <br><small style="color: #dc3545;">(Terlambat)</small>
                                @endif
                            </td>
                            <td>
                                @if($absensi->menit_terlambat > 0)
                                    <span style="color: #dc3545; font-weight: bold;">
                                        {{ $absensi->menit_terlambat }} menit
                                    </span>
                                @else
                                    <span style="color: #28a745;">-</span>
                                @endif
                            </td>
                            <td>
                                @if($absensi->jam_masuk && $absensi->jam_pulang)
                                    {{ number_format($absensi->total_jam_kerja, 1) }} jam
                                @else
                                    <span style="color: #dc3545;">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">
                <p>📋 Tidak ada data absensi untuk periode ini</p>
            </div>
        @endif
    </div>

    @if($absensis->count() > 0)
        <!-- Summary Additional Info -->
        <div class="statistik">
            <h3>📈 INFORMASI TAMBAHAN</h3>
            <table>
                <thead>
                    <tr>
                        <th>Total Hari Kerja</th>
                        <th>Rata-rata Jam Kerja/Hari</th>
                        <th>Total Jam Kerja</th>
                        <th>Tingkat Kedisiplinan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $absensis->count() }} hari</td>
                        <td>
                            @php
                                $avgJamKerja = $absensis->where('jam_masuk', '!=', null)
                                                       ->where('jam_pulang', '!=', null)
                                                       ->avg('total_jam_kerja');
                            @endphp
                            {{ $avgJamKerja ? number_format($avgJamKerja, 1) : '0' }} jam
                        </td>
                        <td>
                            @php
                                $totalJamKerja = $absensis->where('jam_masuk', '!=', null)
                                                         ->where('jam_pulang', '!=', null)
                                                         ->sum('total_jam_kerja');
                            @endphp
                            {{ number_format($totalJamKerja, 1) }} jam
                        </td>
                        <td>
                            @php
                                $tingkatKedisiplinan = $statistik['total_terlambat'] == 0 ? 'Sangat Baik' : 
                                                     ($statistik['total_terlambat'] <= 2 ? 'Baik' : 
                                                     ($statistik['total_terlambat'] <= 5 ? 'Cukup' : 'Perlu Perbaikan'));
                                $colorKedisiplinan = $statistik['total_terlambat'] == 0 ? '#28a745' : 
                                                   ($statistik['total_terlambat'] <= 2 ? '#17a2b8' : 
                                                   ($statistik['total_terlambat'] <= 5 ? '#ffc107' : '#dc3545'));
                            @endphp
                            <span style="color: {{ $colorKedisiplinan }}; font-weight: bold;">
                                {{ $tingkatKedisiplinan }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif

    <div class="signature">
        <table>
            <tr>
                <td style="width: 200px;">
                    <div>{{ now()->locale('id')->translatedFormat('d F Y') }}</div>
                    <div style="margin: 10px 0;">Mengetahui,</div>
                    <div style="margin: 10px 0; font-weight: bold;">Koordinator PMB</div>
                    <div class="sign-box"></div>
                    <div style="border-top: 1px solid #000; margin-top: 5px; padding-top: 5px;">
                        <strong>(.............................)</strong>
                    </div>
                </td>
                <td style="width: 50px;"></td>
                <td style="width: 200px;">
                    <div>&nbsp;</div>
                    <div style="margin: 10px 0;">Karyawan,</div>
                    <div style="margin: 10px 0; font-weight: bold;">{{ $user->jabatan->nama_jabatan }}</div>
                    <div class="sign-box"></div>
                    <div style="border-top: 1px solid #000; margin-top: 5px; padding-top: 5px;">
                        <strong>{{ $user->name }}</strong>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p><strong>SISTEM INFORMASI ABSENSI PMB</strong></p>
        <p>Dokumen ini digenerate secara otomatis pada {{ now()->format('d/m/Y H:i:s') }}</p>
        <p><em>* Slip ini merupakan bukti resmi kehadiran karyawan untuk periode yang tertera</em></p>
    </div>
</body>
</html>