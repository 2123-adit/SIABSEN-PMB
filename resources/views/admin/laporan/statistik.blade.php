@extends('layouts.admin')

@section('title', 'Statistik Kehadiran - SIABSEN PMB')
@section('page-title', 'Statistik Kehadiran')

@push('styles')
<style>
    .stat-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .stat-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 20px;
        border-radius: 10px 10px 0 0;
    }
    .stat-body {
        padding: 20px;
    }
    .progress-custom {
        height: 10px;
        border-radius: 10px;
        background: #e9ecef;
    }
    .progress-bar-custom {
        border-radius: 10px;
        transition: width 0.6s ease;
    }
    .ranking-item {
        display: flex;
        align-items: center;
        padding: 15px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }
    .ranking-item:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    }
    .ranking-badge {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: white;
        margin-right: 15px;
    }
    .rank-1 { background: #ffd700; color: #000; }
    .rank-2 { background: #c0c0c0; color: #000; }
    .rank-3 { background: #cd7f32; }
    .rank-other { background: #6c757d; }
    .chart-container {
        position: relative;
        height: 400px;
        width: 100%;
    }
    .comparison-bar {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }
    .comparison-label {
        width: 150px;
        font-weight: 600;
    }
    .comparison-progress {
        flex: 1;
        margin: 0 15px;
        position: relative;
    }
    .comparison-value {
        font-weight: bold;
        min-width: 60px;
        text-align: right;
    }
</style>
@endpush

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Bulan</label>
                        <select name="bulan" class="form-select">
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tahun</label>
                        <select name="tahun" class="form-select">
                            @for($i = date('Y'); $i >= date('Y') - 3; $i--)
                                <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-bar-chart me-1"></i>
                            Generate Statistik
                        </button>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-success" onclick="exportStatistik()">
                            <i class="bi bi-download me-1"></i>  
                            Export PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Statistik per Jabatan -->
<div class="row">
    <div class="col-12">
        <div class="stat-card">
            <div class="stat-header">
                <h5 class="mb-0">
                    <i class="bi bi-people me-2"></i>
                    Statistik Kehadiran per Jabatan
                </h5>
                <small>Periode: {{ date('F Y', mktime(0, 0, 0, $bulan, 1, $tahun)) }}</small>
            </div>
            <div class="stat-body">
                @foreach($statistikJabatan as $stat)
                    <div class="comparison-bar">
                        <div class="comparison-label">{{ $stat['nama_jabatan'] }}</div>
                        <div class="comparison-progress">
                            <div class="progress-custom">
                                <div class="progress-bar-custom bg-success" 
                                     style="width: {{ $stat['persentase_kehadiran'] }}%"></div>
                            </div>
                            <small class="text-muted">
                                {{ $stat['total_hadir'] }}/{{ $stat['total_user'] * $stat['total_hari_kerja'] }} kehadiran
                            </small>
                        </div>
                        <div class="comparison-value">
                            {{ $stat['persentase_kehadiran'] }}%
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="stat-card">
            <div class="stat-header">
                <h5 class="mb-0">
                    <i class="bi bi-pie-chart me-2"></i>
                    Distribusi Kehadiran
                </h5>
            </div>
            <div class="stat-body">
                <div class="chart-container">
                    <canvas id="pieChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="stat-card">
            <div class="stat-header">
                <h5 class="mb-0">
                    <i class="bi bi-bar-chart me-2"></i>
                    Perbandingan Jabatan
                </h5>
            </div>
            <div class="stat-body">
                <div class="chart-container">
                    <canvas id="barChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ranking Tables -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="stat-card">
            <div class="stat-header">
                <h5 class="mb-0">
                    <i class="bi bi-trophy me-2"></i>
                    Top Jabatan Terdisiplin
                </h5>
            </div>
            <div class="stat-body">
                @foreach($statistikJabatan->sortByDesc('persentase_kehadiran')->take(5) as $index => $stat)
                    <div class="ranking-item">
                        <div class="ranking-badge rank-{{ $index < 3 ? $index + 1 : 'other' }}">
                            {{ $index + 1 }}
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold">{{ $stat['nama_jabatan'] }}</div>
                            <small class="text-muted">{{ $stat['total_user'] }} pegawai</small>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-success">{{ $stat['persentase_kehadiran'] }}%</div>
                            <small class="text-muted">{{ $stat['total_hadir'] }} hadir</small>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="stat-card">
            <div class="stat-header">
                <h5 class="mb-0">
                    <i class="bi bi-clock-history me-2"></i>
                    Jabatan Sering Terlambat
                </h5>
            </div>
            <div class="stat-body">
                @foreach($statistikJabatan->sortByDesc('total_terlambat')->take(5) as $index => $stat)
                    <div class="ranking-item">
                        <div class="ranking-badge rank-other">
                            {{ $index + 1 }}
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold">{{ $stat['nama_jabatan'] }}</div>
                            <small class="text-muted">{{ $stat['total_user'] }} pegawai</small>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-warning">{{ $stat['total_terlambat'] }}</div>
                            <small class="text-muted">kali terlambat</small>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Detail Table -->
<div class="row mt-4">
    <div class="col-12">
        <div class="stat-card">
            <div class="stat-header">
                <h5 class="mb-0">
                    <i class="bi bi-table me-2"></i>
                    Detail Statistik per Jabatan
                </h5>
            </div>
            <div class="stat-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Jabatan</th>
                                <th>Total Pegawai</th>
                                <th>Hari Kerja</th>
                                <th>Hadir</th>
                                <th>Izin</th>
                                <th>Sakit</th>
                                <th>Alfa</th>
                                <th>Terlambat</th>
                                <th>Persentase</th>
                                <th>Tingkat Kedisiplinan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($statistikJabatan as $stat)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $stat['nama_jabatan'] }}</div>
                                    </td>
                                    <td>{{ $stat['total_user'] }}</td>
                                    <td>{{ $stat['total_hari_kerja'] }}</td>
                                    <td>
                                        <span class="badge bg-success">{{ $stat['total_hadir'] }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $stat['total_izin'] }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $stat['total_sakit'] }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">{{ $stat['total_alfa'] }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">{{ $stat['total_terlambat'] }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress me-2" style="width: 100px; height: 8px;">
                                                <div class="progress-bar bg-success" 
                                                     style="width: {{ $stat['persentase_kehadiran'] }}%"></div>
                                            </div>
                                            <span class="fw-bold">{{ $stat['persentase_kehadiran'] }}%</span>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $tingkat = $stat['persentase_kehadiran'];
                                            if ($tingkat >= 90) {
                                                $badge = 'success';
                                                $label = 'Sangat Baik';
                                            } elseif ($tingkat >= 80) {
                                                $badge = 'primary';
                                                $label = 'Baik';
                                            } elseif ($tingkat >= 70) {
                                                $badge = 'warning';
                                                $label = 'Cukup';
                                            } else {
                                                $badge = 'danger';
                                                $label = 'Perlu Perbaikan';
                                            }
                                        @endphp
                                        <span class="badge bg-{{ $badge }}">{{ $label }}</span>
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
    // Pie Chart
    const pieCtx = document.getElementById('pieChart').getContext('2d');
    const pieData = {
        labels: {!! json_encode($statistikJabatan->pluck('nama_jabatan')) !!},
        datasets: [{
            data: {!! json_encode($statistikJabatan->pluck('total_hadir')) !!},
            backgroundColor: [
                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
                '#9966FF', '#FF9F40', '#FF6384', '#36A2EB'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    };

    new Chart(pieCtx, {
        type: 'pie',
        data: pieData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    // Bar Chart
    const barCtx = document.getElementById('barChart').getContext('2d');
    const barData = {
        labels: {!! json_encode($statistikJabatan->pluck('nama_jabatan')) !!},
        datasets: [
            {
                label: 'Hadir',
                data: {!! json_encode($statistikJabatan->pluck('total_hadir')) !!},
                backgroundColor: '#28a745',
                borderColor: '#28a745',
                borderWidth: 1
            },
            {
                label: 'Terlambat',
                data: {!! json_encode($statistikJabatan->pluck('total_terlambat')) !!},
                backgroundColor: '#ffc107',
                borderColor: '#ffc107',
                borderWidth: 1
            }
        ]
    };

    new Chart(barCtx, {
        type: 'bar',
        data: barData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Jabatan'
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Jumlah'
                    },
                    beginAtZero: true
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });

    // Export function
    function exportStatistik() {
        // You can implement PDF export here
        window.print();
    }

    // Animation on load
    document.addEventListener('DOMContentLoaded', function() {
        const progressBars = document.querySelectorAll('.progress-bar-custom');
        progressBars.forEach((bar, index) => {
            setTimeout(() => {
                bar.style.width = bar.style.width;
            }, index * 200);
        });
    });
</script>
@endpush