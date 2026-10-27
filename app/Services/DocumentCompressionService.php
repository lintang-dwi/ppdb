<?php

namespace App\Services;

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Throwable;

class DocumentCompressionService
{
    protected ImageManager $manager;

    public function __construct(?ImageManager $manager = null)
    {
        $this->manager = $manager ?: new ImageManager(new Driver());
    }

    /**
     * Compress an image if it exceeds target dimensions (1600px) or size (800KB).
     * PDFs and non-image files are safely skipped without modification.
     *
     * @return array{compressed: bool, original_size: int, new_size: int, path: string}
     */
    public function compressIfNeeded(string $fullPath, int $sizeThresholdBytes = 819200, int $maxDimension = 1600): array
    {
        if (! file_exists($fullPath)) {
            return ['compressed' => false, 'original_size' => 0, 'new_size' => 0, 'path' => $fullPath];
        }

        $originalSize = (int) filesize($fullPath);
        $mime = mime_content_type($fullPath) ?: '';

        // Safely skip PDFs and non-image types
        if (str_starts_with($mime, 'application/pdf') || ! str_starts_with($mime, 'image/')) {
            return [
                'compressed' => false,
                'original_size' => $originalSize,
                'new_size' => $originalSize,
                'path' => $fullPath,
            ];
        }

        try {
            $image = $this->manager->decodePath($fullPath);
            $width = $image->width();
            $height = $image->height();

            // Check if compression or resize is required
            $needsResize = ($width > $maxDimension || $height > $maxDimension);
            $needsCompression = ($originalSize > $sizeThresholdBytes);

            if (! $needsResize && ! $needsCompression) {
                return [
                    'compressed' => false,
                    'original_size' => $originalSize,
                    'new_size' => $originalSize,
                    'path' => $fullPath,
                ];
            }

            if ($needsResize) {
                $image->scaleDown(width: $maxDimension, height: $maxDimension);
            }

            $image->save($fullPath, 80);

            clearstatcache(true, $fullPath);
            $newSize = (int) filesize($fullPath);

            return [
                'compressed' => true,
                'original_size' => $originalSize,
                'new_size' => $newSize,
                'path' => $fullPath,
            ];
        } catch (Throwable) {
            return [
                'compressed' => false,
                'original_size' => $originalSize,
                'new_size' => $originalSize,
                'path' => $fullPath,
            ];
        }
    }
}
