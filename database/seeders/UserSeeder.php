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
        $adminPassword = 'Admin@PMB2025!'; // CHANGE THIS IN PRODUCTION!
        
        User::create([
            'username' => 'admin',
            'name' => 'Admin PMB',
            'password' => Hash::make($adminPassword),
            'jabatan_id' => 1, // Administrator
            'status' => 'aktif',
            'role' => 'admin',
            'jam_masuk' => '07:30:00',
            'jam_pulang' => '16:30:00'
        ]);
        
        Log::info("Admin created with password: {$adminPassword}");

        // Data karyawan dari Excel dengan PASSWORD KUAT
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
            ['nama' => 'MUHAMAD YUSUF', 'jabatan' => 'TIM PENGOLAHAN', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'SYAIFUL BAHRI', 'jabatan' => 'TIM PENGOLAHAN', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'TUTI MEGAWATI', 'jabatan' => 'TIM PENGOLAHAN', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'DEFI SAPUTRI', 'jabatan' => 'TIM PENGOLAHAN', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'NOVI YANTI', 'jabatan' => 'TIM PENGOLAHAN', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'DESY WAHYUNI', 'jabatan' => 'TIM PENGOLAHAN', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'PUTRI MAHYUNI', 'jabatan' => 'TIM PENGOLAHAN', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'MELDA SAFITRI', 'jabatan' => 'TIM PENGOLAHAN', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'DEWI SARTIKA', 'jabatan' => 'TIM PENGOLAHAN', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'SISKA REVIANA', 'jabatan' => 'TIM PENGOLAHAN', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'DIAN SAFITRI', 'jabatan' => 'TIM PENGOLAHAN', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'SARI INDAH SISKA', 'jabatan' => 'TIM PENGOLAHAN', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'WINDY CHAIRANI', 'jabatan' => 'TIM PENGOLAHAN', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'ROHANI', 'jabatan' => 'TIM PEMORSIAN', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'NURPIATI', 'jabatan' => 'TIM PEMORSIAN', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'ROSMAINI', 'jabatan' => 'TIM PEMORSIAN', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'NURMALA', 'jabatan' => 'TIM PEMORSIAN', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'RINI HAYATI', 'jabatan' => 'TIM PEMORSIAN', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'SITI AMINAH', 'jabatan' => 'TIM PEMORSIAN', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'LILIS SRIWAHYUNI', 'jabatan' => 'TIM PEMORSIAN', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'SURAHMAN', 'jabatan' => 'TIMPENGEMASAN', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'ANDIKA', 'jabatan' => 'TIMPENGEMASAN', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'FAUZI SIREGAR', 'jabatan' => 'TIMPENGEMASAN', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'DEDI KURNIAWAN', 'jabatan' => 'TIMPENGEMASAN', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'ILHAM', 'jabatan' => 'TIM DRIVER', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'HUSEIN', 'jabatan' => 'TIM DRIVER', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'SAMSUL BAHRI', 'jabatan' => 'TIM DRIVER', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'DEDI SAPUTRA', 'jabatan' => 'TIM DRIVER', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'IRFAN', 'jabatan' => 'TIM DRIVER', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'RIAN', 'jabatan' => 'TIM DRIVER', 'jam_masuk' => '11:00', 'jam_pulang' => '08:00'],
            ['nama' => 'SARI DAMAYANTI', 'jabatan' => 'TIM PENCUCIAN ALAT MAKAN', 'jam_masuk' => '08:00', 'jam_pulang' => '17:00'],
            ['nama' => 'SISKA MAHARANI', 'jabatan' => 'TIM PENCUCIAN ALAT MAKAN', 'jam_masuk' => '08:00', 'jam_pulang' => '17:00'],
            ['nama' => 'INTAN SARI', 'jabatan' => 'TIM PENCUCIAN ALAT MAKAN', 'jam_masuk' => '08:00', 'jam_pulang' => '17:00'],
            ['nama' => 'SRI RAHAYU', 'jabatan' => 'TIM KEBERSIHAN', 'jam_masuk' => '06:00', 'jam_pulang' => '15:00'],
            ['nama' => 'FITRI APRIYANI', 'jabatan' => 'TIM KEBERSIHAN', 'jam_masuk' => '06:00', 'jam_pulang' => '15:00'],
            ['nama' => 'ARMEN', 'jabatan' => 'TIM KEMANAN', 'jam_masuk' => '18:00', 'jam_pulang' => '06:00'],
            ['nama' => 'AHMAD YANI', 'jabatan' => 'TIM KEMANAN', 'jam_masuk' => '18:00', 'jam_pulang' => '06:00']
        ];

        $generatedPasswords = [];

        foreach ($karyawanData as $data) {
            // Cari jabatan_id berdasarkan nama jabatan
            $jabatan = Jabatan::where('nama_jabatan', $data['jabatan'])->first();
            
            if ($jabatan) {
                // Generate username dari nama
                $username = strtolower(str_replace(' ', '.', $data['nama']));
                $username = preg_replace('/[^a-z0-9.]/', '', $username);
                
                // Generate strong password
                $password = $this->generateStrongPassword();

                // Handle jam pulang untuk shift malam (misal 04:00 = 28:00 hari sebelumnya)
                $jamPulang = $data['jam_pulang'];
                if ($data['jam_pulang'] === '04:00' || $data['jam_pulang'] === '08:00') {
                    if (strtotime($data['jam_masuk']) > strtotime($data['jam_pulang'])) {
                        // Shift malam, tambah 24 jam ke jam pulang untuk perhitungan
                        $jamPulang = date('H:i', strtotime($data['jam_pulang']) + 24*3600);
                    }
                }

                User::create([
                    'username' => $username,
                    'name' => $data['nama'],
                    'password' => Hash::make($password),
                    'jabatan_id' => $jabatan->id,
                    'status' => 'aktif',
                    'role' => 'user',
                    'jam_masuk' => $data['jam_masuk'] . ':00',
                    'jam_pulang' => $data['jam_pulang'] . ':00'
                ]);

                $generatedPasswords[] = [
                    'username' => $username,
                    'name' => $data['nama'],
                    'password' => $password
                ];
            }
        }
        
        // Save passwords to file for distribution (pastikan file ini aman dan hanya diakses oleh yang berwenang)
        file_put_contents(
            storage_path('app/generated_passwords.json'), 
            json_encode($generatedPasswords, JSON_PRETTY_PRINT)
        );
        
        Log::info("Generated passwords saved to storage/app/generated_passwords.json");
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
