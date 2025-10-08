<?php

use Eclipse\Cms\Models\Banner\Position;
use Eclipse\Cms\Rules\BannerImageDimensionRule;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->position = Position::factory()->create();

    $this->imageType = $this->position->imageTypes()->create([
        'name' => 'Desktop',
        'code' => 'desktop',
        'image_width' => 1200,
        'image_height' => 400,
        'is_hidpi' => false,
    ]);
});

it('validates correct image dimensions', function () {
    $image = imagecreatetruecolor(1200, 400);
    $white = imagecolorallocate($image, 255, 255, 255);
    imagefill($image, 0, 0, $white);
    $tmpFile = tempnam(sys_get_temp_dir(), 'test_image').'.png';
    imagepng($image, $tmpFile);
    imagedestroy($image);

    $uploadedFile = new UploadedFile($tmpFile, 'test.png', 'image/png', null, true);

    $rule = new BannerImageDimensionRule($this->position, $this->imageType->id, false);

    $failed = false;
    $rule->validate('file', $uploadedFile, function () use (&$failed) {
        $failed = true;
    });

    expect($failed)->toBeFalse();

    unlink($tmpFile);
});

it('fails validation for incorrect image dimensions', function () {
    $image = imagecreatetruecolor(800, 300);
    $white = imagecolorallocate($image, 255, 255, 255);
    imagefill($image, 0, 0, $white);
    $tmpFile = tempnam(sys_get_temp_dir(), 'test_image').'.png';
    imagepng($image, $tmpFile);
    imagedestroy($image);

    $uploadedFile = new UploadedFile($tmpFile, 'test.png', 'image/png', null, true);

    $rule = new BannerImageDimensionRule($this->position, $this->imageType->id, false);

    $failed = false;
    $errorMessage = '';

    $rule->validate('file', $uploadedFile, function ($message) use (&$failed, &$errorMessage) {
        $failed = true;
        $errorMessage = $message;
    });

    expect($failed)->toBeTrue();
    expect($errorMessage)->toContain('Image must be exactly 1200×400px. Got 800×300px.');

    unlink($tmpFile);
});

it('validates correct hidpi image dimensions', function () {
    $image = imagecreatetruecolor(2400, 800);
    $white = imagecolorallocate($image, 255, 255, 255);
    imagefill($image, 0, 0, $white);
    $tmpFile = tempnam(sys_get_temp_dir(), 'test_image').'.png';
    imagepng($image, $tmpFile);
    imagedestroy($image);

    $uploadedFile = new UploadedFile($tmpFile, 'test.png', 'image/png', null, true);

    $rule = new BannerImageDimensionRule($this->position, $this->imageType->id, true);

    $failed = false;
    $rule->validate('file', $uploadedFile, function () use (&$failed) {
        $failed = true;
    });

    expect($failed)->toBeFalse();

    unlink($tmpFile);
});

it('fails validation for incorrect hidpi image dimensions', function () {
    $image = imagecreatetruecolor(1200, 400);
    $white = imagecolorallocate($image, 255, 255, 255);
    imagefill($image, 0, 0, $white);
    $tmpFile = tempnam(sys_get_temp_dir(), 'test_image').'.png';
    imagepng($image, $tmpFile);
    imagedestroy($image);

    $uploadedFile = new UploadedFile($tmpFile, 'test.png', 'image/png', null, true);

    $rule = new BannerImageDimensionRule($this->position, $this->imageType->id, true);

    $failed = false;
    $errorMessage = '';

    $rule->validate('file', $uploadedFile, function ($message) use (&$failed, &$errorMessage) {
        $failed = true;
        $errorMessage = $message;
    });

    expect($failed)->toBeTrue();
    expect($errorMessage)->toContain('Image must be exactly 2400×800px. Got 1200×400px.');

    unlink($tmpFile);
});

it('passes validation when no file is provided', function () {
    $rule = new BannerImageDimensionRule($this->position, $this->imageType->id, false);

    $failed = false;
    $rule->validate('file', null, function () use (&$failed) {
        $failed = true;
    });

    expect($failed)->toBeFalse();
});

it('passes validation when image type has no dimensions', function () {
    $imageTypeNoDimensions = $this->position->imageTypes()->create([
        'name' => 'Flexible',
        'code' => 'flexible',
        'image_width' => null,
        'image_height' => null,
        'is_hidpi' => false,
    ]);

    $image = imagecreatetruecolor(500, 300);
    $white = imagecolorallocate($image, 255, 255, 255);
    imagefill($image, 0, 0, $white);
    $tmpFile = tempnam(sys_get_temp_dir(), 'test_image').'.png';
    imagepng($image, $tmpFile);
    imagedestroy($image);

    $uploadedFile = new UploadedFile($tmpFile, 'test.png', 'image/png', null, true);

    $rule = new BannerImageDimensionRule($this->position, $imageTypeNoDimensions->id, false);

    $failed = false;
    $rule->validate('file', $uploadedFile, function () use (&$failed) {
        $failed = true;
    });

    expect($failed)->toBeFalse();

    unlink($tmpFile);
});
