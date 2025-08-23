<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class CleanStoragePhotos extends Command
{
    protected $signature = 'storage:clean-photos {--confirm : Skip confirmation prompt} {--dir= : Specific directory to clean}';
    
    protected $description = 'Clean all photos from storage/app/public directory';

    public function handle()
    {
        $directories = [
            'absensi',
            'profiles', 
            'profile-photos',
            'uploads',
            'photos'
        ];

        // If specific directory is specified
        if ($this->option('dir')) {
            $specificDir = $this->option('dir');
            if (in_array($specificDir, $directories)) {
                $directories = [$specificDir];
                $this->info("🎯 Cleaning specific directory: {$specificDir}");
            } else {
                $this->error("❌ Invalid directory. Available: " . implode(', ', $directories));
                return 1;
            }
        }

        if (!$this->option('confirm')) {
            $dirList = implode(', ', $directories);
            if (!$this->confirm("Are you sure you want to delete all photos from: {$dirList}? This action cannot be undone.")) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $deletedFiles = 0;
        $deletedDirs = 0;

        $this->info('🧹 Starting cleanup process...');
        $this->newLine();

        // Clean specific directories
        foreach ($directories as $dir) {
            $path = storage_path("app/public/{$dir}");
            
            if (File::exists($path)) {
                $files = File::allFiles($path);
                $fileCount = count($files);
                
                if ($fileCount > 0) {
                    // Show files to be deleted
                    $this->info("📁 Directory: {$dir}");
                    foreach ($files as $file) {
                        $filename = $file->getFilename();
                        $size = $this->formatBytes($file->getSize());
                        $this->line("   🗑️  {$filename} ({$size})");
                    }
                    
                    File::deleteDirectory($path);
                    $deletedFiles += $fileCount;
                    $deletedDirs++;
                    $this->info("✅ Deleted {$fileCount} files from {$dir}");
                } else {
                    $this->info("📂 Directory {$dir} is already empty");
                }
            } else {
                $this->info("📂 Directory {$dir} doesn't exist");
            }
        }

        // Clean any other image files in root public storage
        $publicPath = storage_path('app/public');
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        
        $rootFiles = 0;
        foreach ($imageExtensions as $ext) {
            $files = File::glob("{$publicPath}/*.{$ext}");
            foreach ($files as $file) {
                $filename = basename($file);
                $size = $this->formatBytes(filesize($file));
                $this->line("🗑️  Root file: {$filename} ({$size})");
                File::delete($file);
                $deletedFiles++;
                $rootFiles++;
            }
        }

        if ($rootFiles > 0) {
            $this->info("✅ Deleted {$rootFiles} files from root storage");
        }

        $this->newLine();
        $this->info("🎉 Cleanup completed!");
        $this->info("📁 Directories processed: " . count($directories));
        $this->info("🗑️  Total files deleted: {$deletedFiles}");
        
        // Recreate directories
        $this->newLine();
        $this->info('📂 Recreating directories...');
        foreach ($directories as $dir) {
            // Use Storage facade for cross-platform compatibility
            $storagePath = "public/{$dir}";
            
            // Create directory if it doesn't exist
            if (!Storage::exists($storagePath)) {
                Storage::makeDirectory($storagePath);
                $this->info("✅ Created: {$dir}");
            }
            
            // Create .gitkeep using Storage facade
            $gitkeepPath = "{$storagePath}/.gitkeep";
            if (!Storage::exists($gitkeepPath)) {
                Storage::put($gitkeepPath, '');
                $this->info("✅ Added .gitkeep to {$dir}");
            }
        }

        $this->newLine();
        $this->info('🚀 All done! Directories are ready for new uploads.');
        
        return 0;
    }

    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }
}