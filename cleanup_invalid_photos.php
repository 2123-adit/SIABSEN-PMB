<?php
// Script untuk membersihkan foto profil yang tidak valid

require_once 'vendor/autoload.php';

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Storage;

echo "🔍 Checking invalid profile photos...\n";

$users = User::whereNotNull('foto_profil')
    ->where('foto_profil', '!=', '')
    ->get();

$invalidCount = 0;
$fixedCount = 0;

foreach ($users as $user) {
    $photoPath = $user->foto_profil;
    
    // Extract filename dari URL atau path
    $filename = basename($photoPath);
    $fullPath = "profile-photos/$filename";
    
    // Cek apakah file ada di storage
    if (!Storage::disk('public')->exists($fullPath)) {
        echo "❌ User {$user->user_id} ({$user->name}): Missing file {$filename}\n";
        
        // Set foto_profil ke null
        $user->foto_profil = null;
        $user->save();
        
        $invalidCount++;
        $fixedCount++;
        
        echo "✅ Fixed: Set foto_profil to null for {$user->user_id}\n";
    } else {
        echo "✅ User {$user->user_id}: Photo exists\n";
    }
}

echo "\n📊 Summary:\n";
echo "Invalid photos found: $invalidCount\n";
echo "Records fixed: $fixedCount\n";
echo "✅ Cleanup completed!\n";
?>