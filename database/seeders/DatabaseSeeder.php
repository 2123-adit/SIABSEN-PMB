<?php
// database/seeders/DatabaseSeeder.php - UPDATED

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            JabatanSeeder::class,
            UserSeeder::class,
            HolidaySeeder::class,
            GeofencingSeeder::class, // NEW
        ]);
    }
}