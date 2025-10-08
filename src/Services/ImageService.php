<?php

namespace Eclipse\Cms\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Image\Image;

class ImageService
{
    public function createRegularFromHidpi(string $hidpiPath, int $targetWidth, int $targetHeight): string
    {
        if (! Storage::exists($hidpiPath)) {
            throw new Exception("HiDPI image not found: {$hidpiPath}");
        }

        $fullHidpiPath = Storage::path($hidpiPath);

        $pathInfo = pathinfo($hidpiPath);
        $regularPath = $pathInfo['dirname'].'/'.$pathInfo['filename'].'_1x.'.$pathInfo['extension'];
        $fullRegularPath = Storage::path($regularPath);

        $directory = dirname($fullRegularPath);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        try {
            Image::load($fullHidpiPath)
                ->width($targetWidth)
                ->height($targetHeight)
                ->save($fullRegularPath);
        } catch (Exception $e) {
            throw new Exception("Failed to process HiDPI image: {$e->getMessage()}");
        }

        if (! file_exists($fullRegularPath)) {
            throw new Exception("Failed to create regular image: {$fullRegularPath}");
        }

        return $regularPath;
    }

    public function processHidpiUpload(UploadedFile $file, string $directory, int $regularWidth, int $regularHeight): array
    {
        $hidpiPath = $file->store($directory);

        $regularPath = $this->createRegularFromHidpi($hidpiPath, $regularWidth, $regularHeight);

        return [
            'hidpi_path' => $hidpiPath,
            'regular_path' => $regularPath,
        ];
    }
}
