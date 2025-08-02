@extends('layouts.admin')

@section('title', 'Calendar Absensi - SIABSEN PMB')
@section('page-title', 'Calendar Absensi')

@push('styles')
<style>
    .calendar {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .calendar-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 10px 10px 0 0;
    }
    .calendar-nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 1px;
        background: #e9ecef;
        padding: 1px;
    }
    .calendar-day-header {
        background: #f8f9fa;
        padding: 15px 5px;
        text-align: center;
        font-weight: bold;
        color: #495057;
    }
    .calendar-day {
        background: white;
        min-height: 120px;
        padding: 8px;
        position: relative;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .calendar-day:hover {
        background: #f8f9fa;
    }
    .calendar-day.other-month {
        background: #f8f9fa;
        color: #6c757d;
    }
    .calendar-day.today {
        background: #e3f2fd;
        border: 2px solid #2196f3;
    }
    .day-number {
        font-weight: bold;
        font-size: 14px;
        margin-bottom: 5px;
    }
    .attendance-summary {
        font-size: 11px;
        text-align: center;
    }
    .attendance-count {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 10px;
        margin: 1px;
        color: white;
        font-weight: bold;
    }
    .hadir { background: #28a745; }
    .terlambat { background: #ffc107; color: #000; }
    .alfa { background: #dc3545; }
    .legend {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin: 20px 0;
        flex-wrap: wrap;
    }
    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 15px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }
    .month-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }
    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        text-align: center;
    }
    .stat-number {
        font-size: 2rem;
        font-weight: bold;
        color: #667eea;
    }
    .stat-label {
        color: #6c757d;
        margin-top: 5px;
    }
    @media (max-width: 768px) {
        .calendar-grid {
            grid-template-columns: repeat(7, 1fr);
            gap: 0;
        }
        .calendar-day {
            min-height: 80px;
            padding: 4px;
        }
        .attendance-summary {
            font-size: 9px;
        }
    }
</style>
@endpush

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <!-- Filter Bulan/Tahun -->
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Bulan</label>
                        <select name="month" class="form-select">
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tahun</label>
                        <select name="year" class="form-select">
                            @for($i = date('Y'); $i >= date('Y') - 3; $i--)
                                <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i>
                            Tampilkan
                        </button>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.absensi.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-list me-1"></i>
                            View List
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Statistik Bulan -->
<div class="month-stats">
    @php
        $totalHadir = collect($calendar)->sum('total_hadir');
        $totalTerlambat = collect($calendar)->sum('total_terlambat');
        $totalUser = \App\Models\User::where('role', 'user')->where('status', 'aktif')->count();
        $daysInMonth = \Carbon\Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $workingDays = $daysInMonth - collect($calendar)->filter(function($day) {
            return $day['date']->dayOfWeek == 0 || $day['date']->dayOfWeek == 6; // Weekend
        })->count();
        $attendanceRate = $workingDays > 0 && $totalUser > 0 ? round(($totalHadir / ($workingDays * $totalUser)) * 100, 1) : 0;
    @endphp
    
    <div class="stat-card">
        <div class="stat-number">{{ $totalHadir }}</div>
        <div class="stat-label">Total Kehadiran</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $totalTerlambat }}</div>
        <div class="stat-label">Total Terlambat</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $attendanceRate }}%</div>
        <div class="stat-label">Tingkat Kehadiran</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $workingDays }}</div>
        <div class="stat-label">Hari Kerja</div>
    </div>
</div>

<!-- Legend -->
<div class="legend">
    <div class="legend-item">
        <div class="legend-color hadir"></div>
        <span>Hadir</span>
    </div>
    <div class="legend-item">
        <div class="legend-color terlambat"></div>
        <span>Terlambat</span>
    </div>
    <div class="legend-item">
        <div class="legend-color alfa"></div>
        <span>Tidak Hadir</span>
    </div>
    <div class="legend-item">
        <div class="legend-color" style="background: #6c757d;"></div>
        <span>Weekend/Libur</span>
    </div>
</div>

<!-- Calendar -->
<div class="calendar">
    <div class="calendar-header">
        <div class="calendar-nav">
            <a href="?month={{ $month == 1 ? 12 : $month - 1 }}&year={{ $month == 1 ? $year - 1 : $year }}" 
               class="btn btn-outline-light">
                <i class="bi bi-chevron-left"></i>
            </a>
            <h3 class="mb-0">
                {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}
            </h3>
            <a href="?month={{ $month == 12 ? 1 : $month + 1 }}&year={{ $month == 12 ? $year + 1 : $year }}" 
               class="btn btn-outline-light">
                <i class="bi bi-chevron-right"></i>
            </a>
        </div>
        <div class="text-center">
            <small>Klik tanggal untuk melihat detail absensi</small>
        </div>
    </div>
    
    <div class="calendar-grid">
        <!-- Header hari -->
        <div class="calendar-day-header">Min</div>
        <div class="calendar-day-header">Sen</div>
        <div class="calendar-day-header">Sel</div>
        <div class="calendar-day-header">Rab</div>
        <div class="calendar-day-header">Kam</div>
        <div class="calendar-day-header">Jum</div>
        <div class="calendar-day-header">Sab</div>
        
        <!-- Tanggal-tanggal -->
        @php
            $startDate = \Carbon\Carbon::createFromDate($year, $month, 1);
            $startCalendar = $startDate->copy()->startOfWeek(0); // Minggu = 0
            $endDate = $startDate->copy()->endOfMonth();
            $endCalendar = $endDate->copy()->endOfWeek(6); // Sabtu = 6
            $currentDate = $startCalendar->copy();
        @endphp
        
        @while($currentDate <= $endCalendar)
            @php
                $dateString = $currentDate->format('Y-m-d');
                $dayData = $calendar[$dateString] ?? null;
                $isCurrentMonth = $currentDate->month == $month;
                $isToday = $currentDate->isToday();
                $isWeekend = $currentDate->dayOfWeek == 0 || $currentDate->dayOfWeek == 6;
            @endphp
            
            <div class="calendar-day {{ !$isCurrentMonth ? 'other-month' : '' }} {{ $isToday ? 'today' : '' }}"
                 onclick="showDayDetail('{{ $dateString }}')" 
                 data-date="{{ $dateString }}">
                <div class="day-number">{{ $currentDate->day }}</div>
                
                @if($dayData && $isCurrentMonth && !$isWeekend)
                    <div class="attendance-summary">
                        @if($dayData['total_hadir'] > 0)
                            <span class="attendance-count hadir">{{ $dayData['total_hadir'] }}</span>
                        @endif
                        @if($dayData['total_terlambat'] > 0)
                            <span class="attendance-count terlambat">{{ $dayData['total_terlambat'] }}</span>
                        @endif
                        @php
                            $totalAlfa = $totalUser - $dayData['total_hadir'];
                        @endphp
                        @if($totalAlfa > 0)
                            <span class="attendance-count alfa">{{ $totalAlfa }}</span>
                        @endif
                    </div>
                @elseif($isWeekend && $isCurrentMonth)
                    <div class="attendance-summary">
                        <small class="text-muted">Weekend</small>
                    </div>
                @endif
            </div>
            
            @php $currentDate->addDay(); @endphp
        @endwhile
    </div>
</div>

<!-- Modal Detail Hari -->
<div class="modal fade" id="dayDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Absensi - <span id="modalDate"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function showDayDetail(date) {
        $('#modalDate').text(formatDate(date));
        $('#modalBody').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        
        // Show modal
        new bootstrap.Modal(document.getElementById('dayDetailModal')).show();
        
        // Load data via AJAX (you can implement this)
        loadDayDetail(date);
    }
    
    function formatDate(dateString) {
        const date = new Date(dateString);
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        };
        return date.toLocaleDateString('id-ID', options);
    }
    
    function loadDayDetail(date) {
        // Simulate loading - replace with actual AJAX call
        setTimeout(() => {
            const calendarData = @json($calendar);
            const dayData = calendarData[date];
            
            if (dayData && dayData.absensis && dayData.absensis.length > 0) {
                let html = '<div class="table-responsive"><table class="table table-sm">';
                html += '<thead><tr><th>Nama</th><th>Jabatan</th><th>Jam Masuk</th><th>Jam Pulang</th><th>Status</th></tr></thead>';
                html += '<tbody>';
                
                dayData.absensis.forEach((absensi, index) => {
                    const statusBadge = getStatusBadge(absensi.status_kehadiran);
                    html += `<tr>
                        <td>${absensi.user.name}</td>
                        <td>${absensi.user.jabatan.nama_jabatan}</td>
                        <td>${absensi.jam_masuk || '-'}</td>
                        <td>${absensi.jam_pulang || '-'}</td>
                        <td><span class="badge bg-${statusBadge}">${absensi.status_kehadiran}</span></td>
                    </tr>`;
                });
                
                html += '</tbody></table></div>';
            } else {
                html = '<div class="text-center text-muted"><i class="bi bi-calendar-x"></i><br>Tidak ada data absensi</div>';
            }
            
            $('#modalBody').html(html);
        }, 500);
    }
    
    function getStatusBadge(status) {
        const badges = {
            'hadir': 'success',
            'izin': 'warning',
            'sakit': 'info',
            'alfa': 'danger'
        };
        return badges[status] || 'secondary';
    }
</script>
@endpush
