<?php

namespace App\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Trait HasFileStorage
 *
 * Provides a standardized way to upload and delete files for models.
 */
trait HasFileStorage
{
    /**
     * Upload a file and return its path.
     */
    public function uploadFile(UploadedFile $file, string $directory = '', string $disk = 'public'): string
    {
        $path = $file->store($directory, $disk);

        if (! $path) {
            throw new \RuntimeException("Failed to upload file to {$directory}");
        }

        return $path;
    }

    /**
     * Delete a file from storage.
     */
    public function deleteFile(?string $path, string $disk = 'public'): bool
    {
        if ($path && Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }
}
