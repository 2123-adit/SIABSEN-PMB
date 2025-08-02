<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal',
        'nama_libur',
        'deskripsi',
        'jenis',
        'is_active'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'is_active' => 'boolean'
    ];

    public static function isHoliday($date): bool
    {
        return self::where('tanggal', Carbon::parse($date)->format('Y-m-d'))
                   ->where('is_active', true)
                   ->exists();
    }

    public static function getActiveHolidays()
    {
        return self::where('is_active', true)
                   ->orderBy('tanggal', 'asc')
                   ->get();
    }

    public function getJenisLabelAttribute()
    {
        $labels = [
            'nasional' => 'Hari Libur Nasional',
            'cuti_bersama' => 'Cuti Bersama',
            'khusus' => 'Libur Khusus'
        ];

        return $labels[$this->jenis] ?? 'Tidak Diketahui';
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'nasional' => 'danger',
            'cuti_bersama' => 'warning',
            'khusus' => 'info'
        ];

        return $badges[$this->jenis] ?? 'secondary';
    }
}