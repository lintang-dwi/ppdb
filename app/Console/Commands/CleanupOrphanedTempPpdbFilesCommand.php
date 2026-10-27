<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CleanupOrphanedTempPpdbFilesCommand extends Command
{
    protected $signature = 'ppdb:clean-temp {--days=7 : Age threshold in days}';

    protected $description = 'Clean up orphaned temporary PPDB upload files older than specified days';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $thresholdTime = now()->subDays($days)->timestamp;
        $disk = Storage::disk('local');
        $baseDir = 'temp_ppdb';

        if (! $disk->exists($baseDir)) {
            $this->info('Directory temp_ppdb does not exist. Nothing to clean.');

            return self::SUCCESS;
        }

        $allFiles = $disk->allFiles($baseDir);
        $deletedCount = 0;
        $bytesFreed = 0;

        foreach ($allFiles as $filePath) {
            $lastModified = $disk->lastModified($filePath);
            if ($days === 0 || $lastModified < $thresholdTime) {
                $bytesFreed += $disk->size($filePath);
                $disk->delete($filePath);
                $deletedCount++;
            }
        }

        // Clean empty directories
        foreach ($disk->allDirectories($baseDir) as $directory) {
            if (empty($disk->allFiles($directory)) && empty($disk->directories($directory))) {
                $disk->deleteDirectory($directory);
            }
        }

        $mbFreed = round($bytesFreed / 1048576, 2);
        $this->info("Cleaned {$deletedCount} orphaned temp files ({$mbFreed} MB freed).");
        Log::info("PPDB Temp Cleanup: Removed {$deletedCount} files ({$mbFreed} MB).");

        return self::SUCCESS;
    }
}
