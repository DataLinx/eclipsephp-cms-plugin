<?php

use Eclipse\Cms\Models\Banner;
use Eclipse\Cms\Models\Banner\Image;
use Eclipse\Cms\Models\Banner\ImageType;
use Eclipse\Cms\Models\Banner\Position;

it('creates banner position with relationships', function () {
    $position = Position::create([
        'name' => 'Header Banner',
        'code' => 'header',
    ]);

    expect($position->name)->toBe('Header Banner');
    expect($position->code)->toBe('header');
    expect($position->imageTypes())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
    expect($position->banners())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

it('creates image types for position', function () {
    $position = Position::create([
        'name' => 'Header Banner',
        'code' => 'header',
    ]);

    $imageType = ImageType::create([
        'position_id' => $position->id,
        'name' => 'Desktop',
        'code' => 'desktop',
        'image_width' => 1200,
        'image_height' => 400,
        'is_hidpi' => true,
    ]);

    expect($imageType->position_id)->toBe($position->id);
    expect($imageType->name)->toBe('Desktop');
    expect($imageType->is_hidpi)->toBe(true);
    expect($imageType->position)->toBeInstanceOf(Position::class);

    expect($position->imageTypes)->toHaveCount(1);
    expect($position->imageTypes->first()->name)->toBe('Desktop');
});

it('creates banner with images', function () {
    $position = Position::create([
        'name' => 'Header Banner',
        'code' => 'header',
    ]);

    $imageType = ImageType::create([
        'position_id' => $position->id,
        'name' => 'Desktop',
        'code' => 'desktop',
        'image_width' => 1200,
        'image_height' => 400,
        'is_hidpi' => false,
    ]);

    $banner = Banner::create([
        'position_id' => $position->id,
        'name' => 'Test Banner',
        'link' => 'https://example.com',
        'is_active' => true,
        'new_tab' => false,
        'sort' => 1,
    ]);

    $image = Image::create([
        'banner_id' => $banner->id,
        'type_id' => $imageType->id,
        'file' => 'banners/test.jpg',
        'image_width' => 1200,
        'image_height' => 400,
        'is_hidpi' => false,
    ]);

    expect($banner->name)->toBe('Test Banner');
    expect($banner->is_active)->toBe(true);
    expect($banner->new_tab)->toBe(false);
    expect($banner->position)->toBeInstanceOf(Position::class);
    expect($banner->images)->toHaveCount(1);

    expect($image->banner)->toBeInstanceOf(Banner::class);
    expect($image->type)->toBeInstanceOf(ImageType::class);
    expect($image->file)->toBe('banners/test.jpg');
});

it('orders banners by sort field', function () {
    $position = Position::create([
        'name' => 'Header Banner',
        'code' => 'header',
    ]);

    Banner::create([
        'position_id' => $position->id,
        'name' => 'Banner 2',
        'sort' => 2,
        'is_active' => true,
        'new_tab' => false,
    ]);

    Banner::create([
        'position_id' => $position->id,
        'name' => 'Banner 1',
        'sort' => 1,
        'is_active' => true,
        'new_tab' => false,
    ]);

    Banner::create([
        'position_id' => $position->id,
        'name' => 'Banner 3',
        'sort' => 3,
        'is_active' => true,
        'new_tab' => false,
    ]);

    $orderedBanners = $position->banners;

    expect($orderedBanners)->toHaveCount(3);
    expect($orderedBanners->first()->name)->toBe('Banner 1');
    expect($orderedBanners->last()->name)->toBe('Banner 3');
});

it('soft deletes positions and cascades to banners', function () {
    $position = Position::create([
        'name' => 'Header Banner',
        'code' => 'header',
    ]);

    $banner = Banner::create([
        'position_id' => $position->id,
        'name' => 'Test Banner',
        'is_active' => true,
        'new_tab' => false,
        'sort' => 1,
    ]);

    $position->delete();

    expect($position->trashed())->toBe(true);
    expect($banner->fresh()->trashed())->toBe(true);
});
