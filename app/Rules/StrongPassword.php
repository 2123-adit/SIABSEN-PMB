<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class StrongPassword implements Rule
{
    public function passes($attribute, $value)
    {
        // Password must be at least 8 characters and contain:
        // - At least one uppercase letter
        // - At least one lowercase letter  
        // - At least one number
        // - At least one special character
        
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $value);
    }

    public function message()
    {
        return 'Password harus minimal 8 karakter dan mengandung huruf besar, huruf kecil, angka, dan simbol.';
    }
}