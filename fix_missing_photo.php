<?php
// Script untuk fix foto profil yang 404

require_once 'vendor/autoload.php';

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "🔍 Looking for user with missing photo...\n";

$missingFile = 'loKJrAy7tAlHeVkhyPlC0XhcI6ATD42ZwtmCmUfW.jpg';

$users = User::where('foto_profil', 'LIKE', "%$missingFile%")->get();

if ($users->count() > 0) {
    foreach ($users as $user) {
        echo "❌ Found user {$user->user_id} ({$user->name}) with missing photo: {$user->foto_profil}\n";
        
        // Set foto_profil to null
        $user->foto_profil = null;
        $user->save();
        
        echo "✅ Fixed: Set foto_profil to null for {$user->user_id}\n";
    }
} else {
    echo "🔍 No users found with file: $missingFile\n";
    echo "Checking all users with foto_profil...\n";
    
    $allUsers = User::whereNotNull('foto_profil')->get();
    foreach ($allUsers as $user) {
        echo "User {$user->user_id}: {$user->foto_profil}\n";
    }
}

echo "✅ Done!\n";
?>