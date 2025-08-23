<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Jabatan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ADMIN USER - STRONG PASSWORD
        $adminPassword = 'Admin@ADMA2025!'; // CHANGE THIS IN PRODUCTION!
        
        User::create([
            'user_id' => 'ADMIN',
            'name' => 'ADMINISTRATOR',
            'password' => Hash::make($adminPassword),
            'jabatan_id' => 1, // Administrator
            'status' => 'aktif',
            'role' => 'admin',
            'jam_masuk' => '07:30:00',
            'jam_pulang' => '16:30:00',
            'password_changed' => true, // Admin sudah menggunakan password kuat
            'password_changed_at' => now()
        ]);
        
        Log::info("Admin created with password: {$adminPassword}");

        // Data karyawan dari Excel SPPG YKB Polrestabes Medan
        $karyawanData = [
            ['nama' => 'ELVA ROITA SINAGA', 'jabatan' => 'ASISTEN LAPANGAN', 'jam_masuk' => '08:00', 'jam_pulang' => '17:00'],
            ['nama' => 'RIA KURNIA SARI', 'jabatan' => 'TIM PERSIAPAN', 'jam_masuk' => '19:00', 'jam_pulang' => '04:00'],
            ['nama' => 'SITI FLOWERNTA', 'jabatan' => 'TIM PERSIAPAN', 'jam_masuk' => '19:00', 'jam_pulang' => '04:00'],
            ['nama' => 'YAYANG RAMADHANI', 'jabatan' => 'TIM PERSIAPAN', 'jam_masuk' => '19:00', 'jam_pulang' => '04:00'],
            ['nama' => 'SOFIANA', 'jabatan' => 'TIM PERSIAPAN', 'jam_masuk' => '19:00', 'jam_pulang' => '04:00'],
            ['nama' => 'YAN FAHRI PURBA', 'jabatan' => 'TIM PERSIAPAN', 'jam_masuk' => '19:00', 'jam_pulang' => '04:00'],
            ['nama' => 'ROIDAH', 'jabatan' => 'KEPALA KOKI', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'SUKASMI', 'jabatan' => 'TIM PENGOLAHAN', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'JANTY SULAEMAN', 'jabatan' => 'TIM PENGOLAHAN', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'RICKY HIDAYAT', 'jabatan' => 'TIM PENGOLAHAN', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'ERFIANNI NASUTION', 'jabatan' => 'TIM PENGOLAHAN', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'LILIS SINURAT', 'jabatan' => 'TIM PENGOLAHAN', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'ELLY MANDAWATY', 'jabatan' => 'TIM PENGOLAHAN', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'VIVI WAHYUNI', 'jabatan' => 'TIM PENGOLAHAN', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'SRI WULANDARI LUBIS', 'jabatan' => 'TIM PENGOLAHAN', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'IDA ASI MANURUNG', 'jabatan' => 'TIM PEMORSIAN', 'jam_masuk' => '05:00', 'jam_pulang' => '14:00'],
            ['nama' => 'DEBORA GRESIA SIAHAAN', 'jabatan' => 'TIM PEMORSIAN', 'jam_masuk' => '05:00', 'jam_pulang' => '14:00'],
            ['nama' => 'YUNITA SIAHAAN', 'jabatan' => 'TIM PEMORSIAN', 'jam_masuk' => '05:00', 'jam_pulang' => '14:00'],
            ['nama' => 'RISMA SITANGGANG', 'jabatan' => 'TIM PEMORSIAN', 'jam_masuk' => '05:00', 'jam_pulang' => '14:00'],
            ['nama' => 'NURIATI BR SIHOMBING', 'jabatan' => 'TIM PEMORSIAN', 'jam_masuk' => '05:00', 'jam_pulang' => '14:00'],
            ['nama' => 'DANNY HARIKSON SILALAHI', 'jabatan' => 'TIM PEMORSIAN', 'jam_masuk' => '05:00', 'jam_pulang' => '14:00'],
            ['nama' => 'JULAAILA', 'jabatan' => 'TIM PEMORSIAN', 'jam_masuk' => '05:00', 'jam_pulang' => '14:00'],
            ['nama' => 'HOTMAULI SIHOMBING', 'jabatan' => 'TIM PEMORSIAN', 'jam_masuk' => '05:00', 'jam_pulang' => '14:00'],
            ['nama' => 'NORA PARDEDE', 'jabatan' => 'TIM PENGEMASAN', 'jam_masuk' => '05:00', 'jam_pulang' => '14:00'],
            ['nama' => 'JENNY SIHOMBING', 'jabatan' => 'TIM PENGEMASAN', 'jam_masuk' => '05:00', 'jam_pulang' => '14:00'],
            ['nama' => 'MUSLIM AR RAHMAN', 'jabatan' => 'TIM DRIVER', 'jam_masuk' => '07:00', 'jam_pulang' => '15:00'],
            ['nama' => 'TIMBUL PARULIAN BATEE', 'jabatan' => 'TIM DRIVER', 'jam_masuk' => '07:00', 'jam_pulang' => '15:00'],
            ['nama' => 'MARUDUT S.', 'jabatan' => 'TIM DRIVER', 'jam_masuk' => '07:00', 'jam_pulang' => '15:00'],
            ['nama' => 'AMRAN', 'jabatan' => 'TIM DRIVER', 'jam_masuk' => '07:00', 'jam_pulang' => '15:00'],
            ['nama' => 'ADE TUTY SOFYANI', 'jabatan' => 'TIM PENCUCIAN ALAT MAKAN', 'jam_masuk' => '13:00', 'jam_pulang' => '22:00'],
            ['nama' => 'NURSINTA SIMATUPANG', 'jabatan' => 'TIM PENCUCIAN ALAT MAKAN', 'jam_masuk' => '13:00', 'jam_pulang' => '22:00'],
            ['nama' => 'LENNY IMELDA MANURUNG', 'jabatan' => 'TIM PENCUCIAN ALAT MAKAN', 'jam_masuk' => '13:00', 'jam_pulang' => '22:00'],
            ['nama' => 'HERNAWATI SIMANJUNTAK', 'jabatan' => 'TIM PENCUCIAN ALAT MAKAN', 'jam_masuk' => '13:00', 'jam_pulang' => '22:00'],
            ['nama' => 'FITRI CRISTIANI', 'jabatan' => 'TIM PENCUCIAN ALAT MAKAN', 'jam_masuk' => '13:00', 'jam_pulang' => '22:00'],
            ['nama' => 'NUR ELLY SIMATUPANG', 'jabatan' => 'TIM PENCUCIAN ALAT MAKAN', 'jam_masuk' => '13:00', 'jam_pulang' => '22:00'],
            ['nama' => 'TIOMAS BR. SIHOMBING', 'jabatan' => 'TIM PENCUCIAN ALAT MAKAN', 'jam_masuk' => '13:00', 'jam_pulang' => '22:00'],
            ['nama' => 'MISDIANA', 'jabatan' => 'TIM PENCUCIAN ALAT MAKAN', 'jam_masuk' => '13:00', 'jam_pulang' => '22:00'],
            ['nama' => 'RINA ARIANI', 'jabatan' => 'TIM PENCUCIAN ALAT MAKAN', 'jam_masuk' => '13:00', 'jam_pulang' => '22:00'],
            ['nama' => 'MIKA LAOWO', 'jabatan' => 'TIM PENCUCIAN ALAT MAKAN', 'jam_masuk' => '13:00', 'jam_pulang' => '22:00'],
            ['nama' => 'NELLY SISKA TAMBUNAN', 'jabatan' => 'TIM PENCUCIAN ALAT MAKAN', 'jam_masuk' => '13:00', 'jam_pulang' => '22:00'],
            ['nama' => 'RUFIDA BATUBARA', 'jabatan' => 'TIM PENCUCIAN ALAT MAKAN', 'jam_masuk' => '13:00', 'jam_pulang' => '22:00'],
            ['nama' => 'ESTETI MAWATI', 'jabatan' => 'TIM PENCUCIAN ALAT MAKAN', 'jam_masuk' => '13:00', 'jam_pulang' => '22:00'],
            ['nama' => 'MUHAMMAD ZIDANE', 'jabatan' => 'TIM PERSIAPAN', 'jam_masuk' => '19:00', 'jam_pulang' => '04:00'],
            ['nama' => 'ALFA REZI', 'jabatan' => 'TIM KEBERSIHAN', 'jam_masuk' => '07:00', 'jam_pulang' => '16:00'],
            ['nama' => 'JULIUS FRENGKY SINAGA', 'jabatan' => 'TIM PERSIAPAN', 'jam_masuk' => '19:00', 'jam_pulang' => '04:00'],
            ['nama' => 'OSVALDO SITORUS', 'jabatan' => 'TIM KEAMANAN', 'jam_masuk' => '20:00', 'jam_pulang' => '08:00'],
            ['nama' => 'JULIAN EKA SIHOMBING', 'jabatan' => 'TIM KEAMANAN', 'jam_masuk' => '08:00', 'jam_pulang' => '20:00']
        ];

        $generatedPasswords = [];

        foreach ($karyawanData as $index => $data) {
            // Mapping jabatan yang ada di Excel ke jabatan yang ada di database
            $jabatanMapping = [
                'ASISTEN LAPANGAN' => 'ASISTEN LAPANGAN',
                'TIM PERSIAPAN' => 'TIM PERSIAPAN',
                'KEPALA KOKI' => 'KEPALA KOKI',
                'TIM PENGOLAHAN' => 'TIM PENGOLAHAN',
                'TIM PEMORSIAN' => 'TIM PEMORSIAN',
                'TIM PENGEMASAN' => 'TIMPENGEMASAN',   // ✅ diubah
                'TIM DRIVER' => 'TIM DRIVER',
                'TIM PENCUCIAN ALAT MAKAN' => 'TIM PENCUCIAN ALAT MAKAN',
                'TIM KEBERSIHAN' => 'TIM KEBERSIHAN',
                'TIM KEAMANAN' => 'TIM KEMANAN'         // ✅ diubah
            ];

            // Cari jabatan_id berdasarkan mapping
            $namaJabatanDB = $jabatanMapping[$data['jabatan']] ?? $data['jabatan'];
            $jabatan = Jabatan::where('nama_jabatan', $namaJabatanDB)->first();
            
            if ($jabatan) {
                // Generate user_id dengan format ADMA + 3 digit nomor urut
                $employeeNumber = str_pad($index + 1, 3, '0', STR_PAD_LEFT);
                $user_id = 'ADMA' . $employeeNumber; // ADMA001, ADMA002, dst
                
                // Password default = user_id
                $password = $user_id;

                User::create([
                    'user_id' => $user_id,
                    'name' => $data['nama'],
                    'password' => Hash::make($password),
                    'jabatan_id' => $jabatan->id,
                    'status' => 'aktif',
                    'role' => 'user',
                    'jam_masuk' => $data['jam_masuk'] . ':00',
                    'jam_pulang' => $data['jam_pulang'] . ':00',
                    'password_changed' => false, // Belum ganti password
                    'password_changed_at' => null
                ]);

                $generatedPasswords[] = [
                    'user_id' => $user_id,
                    'name' => $data['nama'],
                    'jabatan' => $data['jabatan'],
                    'password' => $password,
                    'jam_kerja' => $data['jam_masuk'] . ':00 - ' . $data['jam_pulang'] . ':00'
                ];
            } else {
                Log::warning("Jabatan tidak ditemukan untuk: {$data['nama']} - {$data['jabatan']}");
            }
        }
        
        // Save user credentials to file for distribution
        file_put_contents(
            storage_path('app/default_passwords.json'), 
            json_encode($generatedPasswords, JSON_PRETTY_PRINT)
        );
        
        Log::info("Seeded " . count($generatedPasswords) . " employees from Excel data");
        Log::info("Default passwords (user_id = password) saved to storage/app/default_passwords.json");
        
        // Log summary by department
        $departments = [];
        foreach ($generatedPasswords as $emp) {
            $dept = $emp['jabatan'];
            $departments[$dept] = ($departments[$dept] ?? 0) + 1;
        }
        
        Log::info("Employee distribution by department:");
        foreach ($departments as $dept => $count) {
            Log::info("- {$dept}: {$count} employees");
        }
    }
    
    private function generateStrongPassword(): string
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $symbols = '@$!%*?&';
        
        $password = '';
        $password .= $uppercase[rand(0, strlen($uppercase) - 1)];
        $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
        $password .= $numbers[rand(0, strlen($numbers) - 1)];
        $password .= $symbols[rand(0, strlen($symbols) - 1)];
        
        // Fill the rest with random characters to reach length 10
        $allChars = $uppercase . $lowercase . $numbers . $symbols;
        for ($i = 4; $i < 10; $i++) {
            $password .= $allChars[rand(0, strlen($allChars) - 1)];
        }
        
        return str_shuffle($password);
    }
}
