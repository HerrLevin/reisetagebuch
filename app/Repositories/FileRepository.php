<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileRepository
{
    public function uploadAndReplaceFile(?string $path, ?UploadedFile $file, ?string $deletePath): string
    {
        $filename = $this->uploadFile($path, $file);
        if ($deletePath) {
            $this->deleteFile($deletePath);
        }

        return $filename;
    }

    public function uploadFile(?string $path, ?UploadedFile $file): string
    {
        if ($path) {
            $filename = uniqid().'.'.$file->getClientOriginalExtension();

            return Storage::disk('public')->putFileAs($path, $file, $filename);
        }

        return '';
    }

    public function deleteFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
