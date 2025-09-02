<?php

namespace Eclipse\Cms\Observers;

use Eclipse\Cms\Models\Banner;
use Eclipse\Cms\Services\ImageService;
use Illuminate\Support\Facades\Storage;

class BannerObserver
{
    public function __construct(
        private ImageService $imageService
    ) {}

    public function created(Banner $banner): void
    {
        $this->processHidpiImages($banner);
    }

    public function updated(Banner $banner): void
    {
        $this->processHidpiImages($banner);
    }

    public function deleting(Banner $banner): void
    {
        $banner->images()->each(function ($image): void {
            $filePath = $image->getTranslation('file', app()->getLocale());
            if ($filePath && Storage::exists($filePath)) {
                Storage::delete($filePath);
            }
        });

        $banner->images()->delete();
    }

    private function processHidpiImages(Banner $banner): void
    {
        $banner->images()->where('is_hidpi', true)->each(function ($hidpiImage) use ($banner) {
            $imageType = $hidpiImage->type;
            if (! $imageType || ! $imageType->image_width || ! $imageType->image_height) {
                return;
            }

            $regularImage = $banner->images()
                ->where('type_id', $hidpiImage->type_id)
                ->where('is_hidpi', false)
                ->first();

            if (! $regularImage) {
                $hidpiFile = $hidpiImage->getTranslation('file', app()->getLocale());
                if ($hidpiFile) {
                    $regularPath = $this->imageService->createRegularFromHidpi(
                        $hidpiFile,
                        $imageType->image_width,
                        $imageType->image_height
                    );

                    $banner->images()->create([
                        'type_id' => $hidpiImage->type_id,
                        'file' => [app()->getLocale() => $regularPath],
                        'is_hidpi' => false,
                        'image_width' => $imageType->image_width,
                        'image_height' => $imageType->image_height,
                    ]);
                }
            }
        });
    }
}
