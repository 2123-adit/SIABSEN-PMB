# CLAUDE.md

File ini memberikan panduan kepada Claude Code (claude.ai/code) saat bekerja dengan kode di repository ini.

## Perintah Development

### Perintah PHP/Laravel
- `composer install` - Instal dependensi PHP
- `php artisan serve` - Jalankan server development
- `php artisan migrate` - Jalankan migrasi database
- `php artisan db:seed` - Seed database
- `php artisan migrate:fresh --seed` - Migrasi fresh dengan seeding
- `php artisan tinker` - Shell interaktif PHP
- `php artisan config:cache` - Cache konfigurasi
- `php artisan route:list` - Tampilkan semua route

### Perintah Frontend
- `npm install` - Instal dependensi Node.js
- `npm run dev` - Jalankan server development Vite
- `npm run build` - Build assets untuk production

### Testing
- `php artisan test` - Jalankan PHPUnit tests
- `vendor/bin/phpunit` - Jalankan PHPUnit langsung
- `vendor/bin/pint` - Jalankan Laravel Pint code formatting

## Arsitektur Sistem

Ini adalah **sistem manajemen absensi berbasis Laravel (ADMA Absensi Kantor)** dengan dukungan API mobile dan fitur geofencing.

### Komponen Utama

**Model Database:**
- `User` - Karyawan dengan relasi jabatan, menyimpan jadwal kerja
- `Absensi` - Record absensi dengan geolokasi, foto, dan tracking status
- `Jabatan` - Posisi pekerjaan dengan jadwal kerja dan toleransi keterlambatan
- `GeofencingSetting` - Batasan lokasi untuk absensi yang valid
- `Holiday` - Konfigurasi hari libur

**Arsitektur API:**
- **Web Routes** (`routes/web.php`) - Panel admin dengan operasi CRUD lengkap
- **API Routes** (`routes/api.php`) - Endpoint aplikasi mobile menggunakan autentikasi Sanctum
- **Controllers**: Namespace Admin dan Api terpisah untuk interface berbeda

**Fitur Utama:**
- **Tracking Absensi** - Check-in/out dengan verifikasi foto dan koordinat GPS
- **Geofencing** - Validasi berbasis lokasi dengan kalkulasi jarak
- **Multiple Entry Methods** - Aplikasi mobile, input manual, import bulk
- **Pelaporan** - Export Excel, slip PDF, analisis statistik
- **Role-based Access** - Peran admin dan karyawan dengan proteksi middleware

### Service Utama
- `AbsensiService` - Logika inti absensi dan validasi
- `ImageHelper` - Utilitas pemrosesan dan penyimpanan foto
- Laravel Sanctum untuk autentikasi API
- Intervention Image untuk manipulasi foto

### Konfigurasi Database
- Menggunakan SQLite secara default (`database/database.sqlite`)
- Timezone dikonfigurasi untuk `Asia/Jakarta`
- Migrasi lengkap dengan foreign key constraints

### Fitur Keamanan
- Rate limiting pada percobaan login (5 per menit)
- HTTPS middleware (`ForceHttps`)
- Security headers middleware
- Validasi password yang kuat
- Proteksi route berbasis role

## Catatan Penting

- Sistem melacak sumber absensi (mobile/manual/bulk) untuk tujuan audit
- Validasi geofencing dilakukan pada check-in dan check-out
- Jadwal kerja didefinisikan pada level jabatan
- Upload foto disimpan di `storage/app/public/absensi/` dengan struktur direktori terorganisir
- Semua timestamp menggunakan timezone `Asia/Jakarta`
- Default admin login: username sesuai dengan data di UserSeeder, password `Admin@ADMA2025!`