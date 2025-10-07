<?php

namespace Eclipse\Cms\Rules;

use Closure;
use Eclipse\Cms\Models\Banner\Position;
use Illuminate\Contracts\Validation\ValidationRule;

class BannerImageDimensionRule implements ValidationRule
{
    public function __construct(
        private Position $position,
        private int $typeId,
        private bool $isHidpi = false
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value) {
            return;
        }

        $imageType = $this->position->imageTypes()->find($this->typeId);

        if (! $imageType || ! $imageType->image_width || ! $imageType->image_height) {
            return;
        }

        $expectedWidth = $this->isHidpi
            ? $imageType->image_width * 2
            : $imageType->image_width;

        $expectedHeight = $this->isHidpi
            ? $imageType->image_height * 2
            : $imageType->image_height;

        $imageSize = getimagesize($value->getPathname());
        $actualWidth = $imageSize[0] ?? 0;
        $actualHeight = $imageSize[1] ?? 0;

        if ($actualWidth !== $expectedWidth || $actualHeight !== $expectedHeight) {
            $fail("Image must be exactly {$expectedWidth}×{$expectedHeight}px. Got {$actualWidth}×{$actualHeight}px.");
        }
    }
}
