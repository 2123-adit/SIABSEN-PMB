<?php
// database/seeders/JabatanSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jabatan;

class JabatanSeeder extends Seeder
{
    public function run(): void
    {
        $jabatans = [
            [
                'nama_jabatan' => 'ASISTEN LAPANGAN',
                'deskripsi' => 'Asisten untuk kegiatan lapangan',
                'toleransi_terlambat' => 15,
                'jadwal_kerja' => [
                    'senin' => true,
                    'selasa' => true,
                    'rabu' => true,
                    'kamis' => true,
                    'jumat' => true
                ],
                'keterangan_jadwal' => 'Senin - Sabtu'
            ],
            [
                'nama_jabatan' => 'TIM PERSIAPAN',
                'deskripsi' => 'Tim yang bertugas melakukan persiapan',
                'toleransi_terlambat' => 15,
                'jadwal_kerja' => [
                    'senin' => true,
                    'selasa' => true,
                    'rabu' => true,
                    'kamis' => true,
                    'jumat' => true
                ],
                'keterangan_jadwal' => 'Setiap hari (shift malam)'
            ],
            [
                'nama_jabatan' => 'KEPALA KOKI',
                'deskripsi' => 'Kepala bagian dapur dan memasak',
                'toleransi_terlambat' => 15,
                'jadwal_kerja' => [
                    'senin' => true,
                    'selasa' => true,
                    'rabu' => true,
                    'kamis' => true,
                    'jumat' => true
                ],
                'keterangan_jadwal' => 'Setiap hari'
            ],
            [
                'nama_jabatan' => 'TIM PENGOLAHAN',
                'deskripsi' => 'Tim yang bertugas mengolah bahan',
                'toleransi_terlambat' => 15,
                'jadwal_kerja' => [
                    'senin' => true,
                    'selasa' => true,
                    'rabu' => true,
                    'kamis' => true,
                    'jumat' => true
                ],
                'keterangan_jadwal' => 'Setiap hari'
            ],
            [
                'nama_jabatan' => 'TIM PEMORSIAN',
                'deskripsi' => 'Tim yang bertugas memorsikan makanan',
                'toleransi_terlambat' => 15,
                'jadwal_kerja' => [
                    'senin' => true,
                    'selasa' => true,
                    'rabu' => true,
                    'kamis' => true,
                    'jumat' => true
                ],
                'keterangan_jadwal' => 'Setiap hari'
            ],
            [
                'nama_jabatan' => 'TIMPENGEMASAN',
                'deskripsi' => 'Tim yang bertugas mengemas produk',
                'toleransi_terlambat' => 15,
                'jadwal_kerja' => [
                    'senin' => true,
                    'selasa' => true,
                    'rabu' => true,
                    'kamis' => true,
                    'jumat' => true
                ],
                'keterangan_jadwal' => 'Setiap hari'
            ],
            [
                'nama_jabatan' => 'TIM DRIVER',
                'deskripsi' => 'Tim driver untuk pengantaran',
                'toleransi_terlambat' => 15,
                'jadwal_kerja' => [
                    'senin' => true,
                    'selasa' => true,
                    'rabu' => true,
                    'kamis' => true,
                    'jumat' => true
                ],
                'keterangan_jadwal' => 'Setiap hari'
            ],
            [
                'nama_jabatan' => 'TIM PENCUCIAN ALAT MAKAN',
                'deskripsi' => 'Tim yang bertugas mencuci alat makan',
                'toleransi_terlambat' => 15,
                'jadwal_kerja' => [
                    'senin' => true,
                    'selasa' => true,
                    'rabu' => true,
                    'kamis' => true,
                    'jumat' => true
                ],
                'keterangan_jadwal' => 'Senin - Jumat'
            ],
            [
                'nama_jabatan' => 'TIM KEBERSIHAN',
                'deskripsi' => 'Tim yang bertugas menjaga kebersihan',
                'toleransi_terlambat' => 15,
                'jadwal_kerja' => [
                    'senin' => true,
                    'selasa' => true,
                    'rabu' => true,
                    'kamis' => true,
                    'jumat' => true
                ],
                'keterangan_jadwal' => 'Senin - Sabtu'
            ],
            [
                'nama_jabatan' => 'TIM KEMANAN',
                'deskripsi' => 'Tim keamanan dan keselamatan',
                'toleransi_terlambat' => 15,
                'jadwal_kerja' => [
                    'senin' => true,
                    'selasa' => true,
                    'rabu' => true,
                    'kamis' => true,
                    'jumat' => true,
                    'sabtu' => true,
                    'minggu' => true
                ],
                'keterangan_jadwal' => 'Setiap hari (24/7)'
            ]
        ];

        foreach ($jabatans as $jabatan) {
            Jabatan::create($jabatan);
        }
    }
}
