<?php

use Eclipse\Cms\Models\Banner;
use Eclipse\Cms\Models\Banner\Position;
use Eclipse\Cms\Services\ImageService;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->setUpSuperAdmin();
    Storage::fake('public');

    $this->position = Position::factory()->create([
        'name' => 'Header Banner',
        'code' => 'header',
    ]);

    $this->hidpiImageType = $this->position->imageTypes()->create([
        'name' => 'Mobile',
        'code' => 'mobile',
        'image_width' => 800,
        'image_height' => 400,
        'is_hidpi' => true,
    ]);

    $this->regularImageType = $this->position->imageTypes()->create([
        'name' => 'Desktop',
        'code' => 'desktop',
        'image_width' => 1200,
        'image_height' => 600,
        'is_hidpi' => false,
    ]);
});

it('generates correct path for 1x image from 2x', function () {
    $hidpiPath = 'banners/test@2x.png';
    $pathInfo = pathinfo($hidpiPath);
    $expectedRegularPath = $pathInfo['dirname'].'/'.$pathInfo['filename'].'_1x.'.$pathInfo['extension'];

    expect($expectedRegularPath)->toBe('banners/test@2x_1x.png');
});

it('only requires 2x upload for hidpi image types', function () {
    $hidpiImageTypes = $this->position->imageTypes()->where('is_hidpi', true)->get();
    $regularImageTypes = $this->position->imageTypes()->where('is_hidpi', false)->get();

    expect($hidpiImageTypes)->toHaveCount(1);
    expect($regularImageTypes)->toHaveCount(1);

    $hidpiType = $hidpiImageTypes->first();
    expect($hidpiType->is_hidpi)->toBeTrue();
    expect($hidpiType->image_width)->toBe(800);
    expect($hidpiType->image_height)->toBe(400);
});

it('creates only hidpi image in seeder for hidpi types', function () {
    $banner = Banner::factory()->create([
        'position_id' => $this->position->id,
    ]);

    $banner->images()->create([
        'type_id' => $this->hidpiImageType->id,
        'file' => ['en' => 'banners/mobile@2x.png'],
        'is_hidpi' => true,
        'image_width' => 1600,
        'image_height' => 800,
    ]);

    $hidpiImages = $banner->images()->where('is_hidpi', true)->get();
    $regularImages = $banner->images()->where('is_hidpi', false)->get();

    expect($hidpiImages)->toHaveCount(1);
    expect($regularImages)->toHaveCount(0);
});

it('creates correct image structure for hidpi types', function () {
    $banner = Banner::factory()->create([
        'position_id' => $this->position->id,
    ]);

    $banner->images()->create([
        'type_id' => $this->hidpiImageType->id,
        'file' => ['en' => 'banners/test@2x.png'],
        'is_hidpi' => true,
        'image_width' => 1600,
        'image_height' => 800,
    ]);

    $hidpiImages = $banner->images()->where('is_hidpi', true)->get();
    expect($hidpiImages)->toHaveCount(1);

    $hidpiImage = $hidpiImages->first();
    expect($hidpiImage->type_id)->toBe($this->hidpiImageType->id);
    expect($hidpiImage->image_width)->toBe(1600);
    expect($hidpiImage->image_height)->toBe(800);
});

it('automatically generates 1x image when hidpi image is created via observer', function () {
    $mockImageService = $this->mock(ImageService::class);
    $mockImageService->shouldReceive('createRegularFromHidpi')
        ->with('banners/test@2x.png', 800, 400)
        ->andReturn('banners/test@2x_1x.png');

    $banner = Banner::factory()->create([
        'position_id' => $this->position->id,
    ]);

    expect($banner->images()->count())->toBe(0);

    $banner->images()->create([
        'type_id' => $this->hidpiImageType->id,
        'file' => ['en' => 'banners/test@2x.png'],
        'is_hidpi' => true,
        'image_width' => 1600,
        'image_height' => 800,
    ]);

    $observer = app(\Eclipse\Cms\Observers\BannerObserver::class);
    $observer->updated($banner);

    expect($banner->images()->where('is_hidpi', true)->count())->toBe(1);
    expect($banner->images()->where('is_hidpi', false)->count())->toBe(1);

    $regularImage = $banner->images()->where('is_hidpi', false)->first();
    expect($regularImage->type_id)->toBe($this->hidpiImageType->id);
    expect($regularImage->image_width)->toBe(800);
    expect($regularImage->image_height)->toBe(400);
    expect($regularImage->getTranslation('file', 'en'))->toBe('banners/test@2x_1x.png');
});
