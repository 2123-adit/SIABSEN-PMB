@extends('layouts.admin')

@section('title', 'Dashboard - SIABSEN PMB')
@section('page-title', 'Dashboard')

@section('content')
<div class="row">
    <!-- Statistik Cards -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Pegawai
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalUsers }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people text-primary" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Hadir Hari Ini
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalAbsenHariIni }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Terlambat Hari Ini
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalTerlambatHariIni }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-clock text-warning" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Persentase Kehadiran
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $persentaseKehadiran }}%</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-graph-up text-info" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Grafik Kehadiran -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-bar-chart me-2"></i>
                    Grafik Kehadiran 7 Hari Terakhir
                </h6>
            </div>
            <div class="card-body">
                <canvas id="chartKehadiran"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Disiplin -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">
                    <i class="bi bi-trophy me-2"></i>
                    Top 5 Terdisiplin
                </h6>
            </div>
            <div class="card-body">
                @forelse($topDisiplin as $index => $user)
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <span class="badge bg-success">{{ $index + 1 }}</span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fw-bold">{{ $user->name }}</div>
                            <small class="text-muted">{{ $user->jabatan->nama_jabatan }}</small>
                        </div>
                        <div class="flex-shrink-0">
                            <span class="badge bg-primary">{{ $user->total_hadir }} hari</span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center">Belum ada data</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Statistik per Jabatan -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-people me-2"></i>
                    Statistik Kehadiran per Jabatan
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Jabatan</th>
                                <th>Total Pegawai</th>
                                <th>Total Hadir Bulan Ini</th>
                                <th>Total Terlambat</th>
                                <th>Persentase Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($statistikJabatan as $stat)
                                <tr>
                                    <td>{{ $stat->nama_jabatan }}</td>
                                    <td>{{ $stat->total_user }}</td>
                                    <td>{{ $stat->total_hadir }}</td>
                                    <td>{{ $stat->total_terlambat }}</td>
                                    <td>
                                        @php
                                            $percentage = $stat->total_user > 0 && $stat->total_hadir > 0 
                                                ? round(($stat->total_hadir / ($stat->total_user * date('j'))) * 100, 2) 
                                                : 0;
                                        @endphp
                                        <span class="badge bg-{{ $percentage >= 80 ? 'success' : ($percentage >= 60 ? 'warning' : 'danger') }}">
                                            {{ $percentage }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Chart Kehadiran
    const ctxKehadiran = document.getElementById('chartKehadiran').getContext('2d');
    const chartKehadiran = new Chart(ctxKehadiran, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($grafikKehadiran, 'tanggal')) !!},
            datasets: [{
                label: 'Jumlah Hadir',
                data: {!! json_encode(array_column($grafikKehadiran, 'hadir')) !!},
                borderColor: 'rgb(102, 126, 234)',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endpush