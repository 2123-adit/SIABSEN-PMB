<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ImageHelper
{
    /**
     * Generate secure image URL
     */
    public static function secureImageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        // Check if file exists
        if (!Storage::disk('public')->exists($path)) {
            return null;
        }

        // For admin users - direct access for now, implement secure later
        if (Auth::check() && Auth::user()->role === 'admin') {
            return asset('storage/' . $path);
        }

        return null;
    }

    /**
     * Get placeholder image for missing files
     */
    public static function placeholderUrl(string $type = 'profile'): string
    {
        $placeholders = [
            'profile' => '/images/placeholder-profile.png',
            'absensi' => '/images/placeholder-absensi.png',
        ];

        return asset($placeholders[$type] ?? $placeholders['profile']);
    }

    /**
     * Check if image file exists
     */
    public static function imageExists(?string $path): bool
    {
        if (!$path) {
            return false;
        }

        return Storage::disk('public')->exists($path);
    }

    /**
     * Get image size info
     */
    public static function getImageInfo(?string $path): ?array
    {
        if (!$path || !Storage::disk('public')->exists($path)) {
            return null;
        }

        $fullPath = Storage::disk('public')->path($path);
        $imageInfo = getimagesize($fullPath);
        
        if (!$imageInfo) {
            return null;
        }

        return [
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'type' => $imageInfo['mime'],
            'size' => Storage::disk('public')->size($path)
        ];
    }
}
