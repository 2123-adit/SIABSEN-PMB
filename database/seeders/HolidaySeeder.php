<?php
// database/seeders/HolidaySeeder.php - Update ini

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Holiday;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        $holidays = [
            // Tahun 2025 - SEMUA DEFAULT NONAKTIF
            [
                'tanggal' => '2025-01-01',
                'nama_libur' => 'Tahun Baru Masehi',
                'deskripsi' => 'Hari libur nasional tahun baru',
                'jenis' => 'nasional',
                'is_active' => false // DEFAULT NONAKTIF
            ],
            [
                'tanggal' => '2025-01-29',
                'nama_libur' => 'Tahun Baru Imlek',
                'deskripsi' => 'Hari libur nasional tahun baru Imlek',
                'jenis' => 'nasional',
                'is_active' => false
            ],
            [
                'tanggal' => '2025-03-14',
                'nama_libur' => 'Hari Suci Nyepi',
                'deskripsi' => 'Hari libur nasional Nyepi',
                'jenis' => 'nasional',
                'is_active' => false
            ],
            [
                'tanggal' => '2025-03-29',
                'nama_libur' => 'Wafat Isa Almasih',
                'deskripsi' => 'Hari libur nasional Jumat Agung',
                'jenis' => 'nasional',
                'is_active' => false
            ],
            [
                'tanggal' => '2025-03-30',
                'nama_libur' => 'Hari Raya Idul Fitri',
                'deskripsi' => 'Hari libur nasional Idul Fitri hari pertama',
                'jenis' => 'nasional',
                'is_active' => false
            ],
            [
                'tanggal' => '2025-03-31',
                'nama_libur' => 'Hari Raya Idul Fitri',
                'deskripsi' => 'Hari libur nasional Idul Fitri hari kedua',
                'jenis' => 'nasional',
                'is_active' => false
            ],
            [
                'tanggal' => '2025-05-01',
                'nama_libur' => 'Hari Buruh',
                'deskripsi' => 'Hari libur nasional Hari Buruh',
                'jenis' => 'nasional',
                'is_active' => false
            ],
            [
                'tanggal' => '2025-05-08',
                'nama_libur' => 'Kenaikan Isa Almasih',
                'deskripsi' => 'Hari libur nasional Kenaikan Yesus',
                'jenis' => 'nasional',
                'is_active' => false
            ],
            [
                'tanggal' => '2025-05-12',
                'nama_libur' => 'Hari Raya Waisak',
                'deskripsi' => 'Hari libur nasional Waisak',
                'jenis' => 'nasional',
                'is_active' => false
            ],
            [
                'tanggal' => '2025-06-01',
                'nama_libur' => 'Hari Lahir Pancasila',
                'deskripsi' => 'Hari libur nasional Hari Lahir Pancasila',
                'jenis' => 'nasional',
                'is_active' => false
            ],
            [
                'tanggal' => '2025-06-06',
                'nama_libur' => 'Hari Raya Idul Adha',
                'deskripsi' => 'Hari libur nasional Idul Adha',
                'jenis' => 'nasional',
                'is_active' => false
            ],
            [
                'tanggal' => '2025-08-17',
                'nama_libur' => 'Hari Kemerdekaan RI',
                'deskripsi' => 'Hari libur nasional HUT RI ke-80',
                'jenis' => 'nasional',
                'is_active' => false
            ],
            [
                'tanggal' => '2025-08-26',
                'nama_libur' => 'Tahun Baru Islam',
                'deskripsi' => 'Hari libur nasional 1 Muharram',
                'jenis' => 'nasional',
                'is_active' => false
            ],
            [
                'tanggal' => '2025-11-05',
                'nama_libur' => 'Maulid Nabi Muhammad SAW',
                'deskripsi' => 'Hari libur nasional Maulid Nabi',
                'jenis' => 'nasional',
                'is_active' => false
            ],
            [
                'tanggal' => '2025-12-25',
                'nama_libur' => 'Hari Raya Natal',
                'deskripsi' => 'Hari libur nasional Natal',
                'jenis' => 'nasional',
                'is_active' => false
            ],
            // Cuti bersama contoh
            [
                'tanggal' => '2025-04-01',
                'nama_libur' => 'Cuti Bersama Idul Fitri',
                'deskripsi' => 'Cuti bersama setelah Idul Fitri',
                'jenis' => 'cuti_bersama',
                'is_active' => false
            ],
            [
                'tanggal' => '2025-04-02',
                'nama_libur' => 'Cuti Bersama Idul Fitri',
                'deskripsi' => 'Cuti bersama setelah Idul Fitri',
                'jenis' => 'cuti_bersama',
                'is_active' => false
            ],
            [
                'tanggal' => '2025-12-24',
                'nama_libur' => 'Cuti Bersama Natal',
                'deskripsi' => 'Cuti bersama sebelum Natal',
                'jenis' => 'cuti_bersama',
                'is_active' => false
            ],
            [
                'tanggal' => '2025-12-31',
                'nama_libur' => 'Cuti Bersama Tahun Baru',
                'deskripsi' => 'Cuti bersama sebelum tahun baru',
                'jenis' => 'cuti_bersama',
                'is_active' => false
            ]
        ];

        foreach ($holidays as $holiday) {
            Holiday::create($holiday);
        }
    }
}