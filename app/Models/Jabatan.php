<?php
// app/Models/Jabatan.php - UPDATED

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Jabatan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_jabatan',
        'deskripsi',
        'toleransi_terlambat',
        'jadwal_kerja',        // NEW
        'keterangan_jadwal'    // NEW
    ];

    protected $casts = [
        'jadwal_kerja' => 'array'  // NEW: Cast JSON ke array
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function getTotalUserAttribute()
    {
        return $this->users()->where('status', 'aktif')->count();
    }

    // NEW: Check apakah jabatan ini kerja pada hari tertentu
    public function isWorkingDay($date): bool
    {
        $carbon = Carbon::parse($date);
        $dayName = strtolower($carbon->locale('id')->dayName);
        
        // Mapping hari Indonesia ke English untuk konsistensi
        $dayMapping = [
            'minggu' => 'minggu',
            'senin' => 'senin',
            'selasa' => 'selasa',
            'rabu' => 'rabu',
            'kamis' => 'kamis',
            'jumat' => 'jumat',
            'sabtu' => 'sabtu'
        ];
        
        // Jika jadwal_kerja null, default senin-jumat
        if (!$this->jadwal_kerja) {
            return !in_array($carbon->dayOfWeek, [0, 6]); // 0=Minggu, 6=Sabtu
        }
        
        return $this->jadwal_kerja[$dayName] ?? false;
    }

    // NEW: Get working days count dalam periode
    public function getWorkingDaysCount($startDate, $endDate): int
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $count = 0;
        
        while ($start <= $end) {
            if ($this->isWorkingDay($start)) {
                $count++;
            }
            $start->addDay();
        }
        
        return $count;
    }

    // NEW: Get jadwal display text
    public function getJadwalDisplayAttribute(): string
    {
        if (!$this->jadwal_kerja) {
            return 'Senin - Jumat';
        }
        
        $days = [];
        $dayNames = [
            'senin' => 'Sen',
            'selasa' => 'Sel', 
            'rabu' => 'Rab',
            'kamis' => 'Kam',
            'jumat' => 'Jum',
            'sabtu' => 'Sab',
            'minggu' => 'Min'
        ];
        
        foreach ($this->jadwal_kerja as $day => $working) {
            if ($working) {
                $days[] = $dayNames[$day] ?? $day;
            }
        }
        
        if (empty($days)) {
            return 'Tidak ada jadwal';
        }
        
        // Format output yang rapi
        if (count($days) == 7) {
            return 'Setiap Hari';
        } elseif (count($days) == 5 && !in_array('Sab', $days) && !in_array('Min', $days)) {
            return 'Sen - Jum';
        } elseif (count($days) == 6 && !in_array('Min', $days)) {
            return 'Sen - Sab';
        } else {
            return implode(', ', $days);
        }
    }
}