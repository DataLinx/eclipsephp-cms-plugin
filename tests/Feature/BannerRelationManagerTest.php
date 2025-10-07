<?php

use Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\RelationManagers\BannerRelationManager;
use Eclipse\Cms\Models\Banner;
use Eclipse\Cms\Models\Banner\Position;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->setUpSuperAdmin();

    $this->position = Position::factory()->create([
        'name' => 'Header Banner',
        'code' => 'header',
    ]);

    $this->position->imageTypes()->createMany([
        [
            'name' => 'Desktop',
            'code' => 'desktop',
            'image_width' => 1200,
            'image_height' => 400,
            'is_hidpi' => false,
        ],
        [
            'name' => 'Mobile',
            'code' => 'mobile',
            'image_width' => 600,
            'image_height' => 300,
            'is_hidpi' => true,
        ],
    ]);
});

it('can list banners in relation manager', function () {
    $banners = Banner::factory()->count(3)->create([
        'position_id' => $this->position->id,
    ]);

    livewire(BannerRelationManager::class, [
        'ownerRecord' => $this->position,
        'pageClass' => 'Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\Pages\EditBannerPosition',
    ])
        ->assertCanSeeTableRecords($banners);
});

it('can create banner through relation manager', function () {
    $newData = Banner::factory()->make([
        'position_id' => $this->position->id,
    ]);

    livewire(BannerRelationManager::class, [
        'ownerRecord' => $this->position,
        'pageClass' => 'Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\Pages\EditBannerPosition',
    ])
        ->callTableAction('create', data: [
            'name' => $newData->name,
            'link' => $newData->link,
            'is_active' => $newData->is_active,
            'new_tab' => $newData->new_tab,
            'images' => [], // Don't create images in tests
        ])
        ->assertHasNoTableActionErrors();

    $banner = Banner::where('position_id', $this->position->id)->first();
    expect($banner)->not->toBeNull()
        ->and($banner->getTranslation('name', 'en'))->toBe($newData->name);
});

it('can edit banner through relation manager', function () {
    $banner = Banner::factory()->create([
        'position_id' => $this->position->id,
    ]);

    $newData = Banner::factory()->make();

    livewire(BannerRelationManager::class, [
        'ownerRecord' => $this->position,
        'pageClass' => 'Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\Pages\EditBannerPosition',
    ])
        ->callTableAction('edit', $banner, data: [
            'name' => $newData->name,
            'link' => $newData->link,
            'is_active' => $newData->is_active,
            'new_tab' => $newData->new_tab,
        ])
        ->assertHasNoTableActionErrors();

    expect($banner->refresh())
        ->name->toBe($newData->name)
        ->link->toBe($newData->link)
        ->is_active->toBe($newData->is_active)
        ->new_tab->toBe($newData->new_tab);
});

it('can delete banner through relation manager', function () {
    $banner = Banner::factory()->create([
        'position_id' => $this->position->id,
    ]);

    livewire(BannerRelationManager::class, [
        'ownerRecord' => $this->position,
        'pageClass' => 'Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\Pages\EditBannerPosition',
    ])
        ->callTableAction('delete', $banner);

    $this->assertSoftDeleted($banner);
});

it('can view banner through relation manager', function () {
    $banner = Banner::factory()->create([
        'position_id' => $this->position->id,
    ]);

    livewire(BannerRelationManager::class, [
        'ownerRecord' => $this->position,
        'pageClass' => 'Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\Pages\EditBannerPosition',
    ])
        ->callTableAction('view', $banner)
        ->assertHasNoTableActionErrors();
});

it('can filter banners by active status', function () {
    $activeBanner = Banner::factory()->create([
        'position_id' => $this->position->id,
        'is_active' => true,
    ]);

    $inactiveBanner = Banner::factory()->create([
        'position_id' => $this->position->id,
        'is_active' => false,
    ]);

    livewire(BannerRelationManager::class, [
        'ownerRecord' => $this->position,
        'pageClass' => 'Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\Pages\EditBannerPosition',
    ])
        ->assertCanSeeTableRecords([$activeBanner, $inactiveBanner])
        ->filterTable('is_active', true)
        ->assertCanSeeTableRecords([$activeBanner])
        ->assertCanNotSeeTableRecords([$inactiveBanner]);
});

it('can search banners by name', function () {
    $banners = Banner::factory()->count(3)->create([
        'position_id' => $this->position->id,
    ]);

    $firstBanner = $banners->first();
    $bannerName = is_array($firstBanner->name) ? $firstBanner->name['en'] ?? $firstBanner->name : $firstBanner->name;

    livewire(BannerRelationManager::class, [
        'ownerRecord' => $this->position,
        'pageClass' => 'Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\Pages\EditBannerPosition',
    ])
        ->searchTable($bannerName)
        ->assertCanSeeTableRecords($banners->take(1));
});

it('auto-increments sort order on creation', function () {
    Banner::query()->forceDelete();

    $existingBanner = Banner::factory()->create([
        'position_id' => $this->position->id,
        'sort' => 5,
    ]);

    $currentMaxSort = Banner::where('position_id', $this->position->id)->max('sort');
    expect($currentMaxSort)->toBe(5);

    $newData = Banner::factory()->make();

    livewire(BannerRelationManager::class, [
        'ownerRecord' => $this->position,
        'pageClass' => 'Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\Pages\EditBannerPosition',
    ])
        ->callTableAction('create', data: [
            'name' => $newData->name,
            'link' => $newData->link,
            'is_active' => $newData->is_active,
            'new_tab' => $newData->new_tab,
            'images' => [], // Don't create images in tests
        ])
        ->assertHasNoTableActionErrors();

    $newBanner = Banner::where('position_id', $this->position->id)
        ->where('sort', '>', 5)
        ->first();
    expect($newBanner)->not->toBeNull()
        ->and($newBanner->sort)->toBe(6);
});

it('validates banner creation', function () {
    livewire(BannerRelationManager::class, [
        'ownerRecord' => $this->position,
        'pageClass' => 'Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\Pages\EditBannerPosition',
    ])
        ->callTableAction('create', data: [
            'name' => null,
        ])
        ->assertHasTableActionErrors(['name' => 'required']);
});

it('can reorder banners', function () {
    $banner1 = Banner::factory()->create([
        'position_id' => $this->position->id,
        'sort' => 1,
    ]);

    $banner2 = Banner::factory()->create([
        'position_id' => $this->position->id,
        'sort' => 2,
    ]);

    livewire(BannerRelationManager::class, [
        'ownerRecord' => $this->position,
        'pageClass' => 'Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\Pages\EditBannerPosition',
    ])
        ->assertCanSeeTableRecords([$banner1, $banner2]);
    // Note: Testing actual reordering would require table action testing
});

it('has trashed filter available', function () {
    $banner = Banner::factory()->create([
        'position_id' => $this->position->id,
    ]);

    livewire(BannerRelationManager::class, [
        'ownerRecord' => $this->position,
        'pageClass' => 'Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\Pages\EditBannerPosition',
    ])
        ->assertTableFilterExists('trashed')
        ->assertCanSeeTableRecords([$banner]);
});

it('triggers observer for hidpi image processing', function () {
    // Create HiDPI image type
    $hidpiImageType = $this->position->imageTypes()->create([
        'name' => 'Mobile HiDPI',
        'code' => 'mobile_hidpi',
        'image_width' => 600,
        'image_height' => 300,
        'is_hidpi' => true,
    ]);

    // Create a banner manually first
    $banner = Banner::factory()->create([
        'position_id' => $this->position->id,
    ]);

    // Add a HiDPI image manually
    $banner->images()->create([
        'type_id' => $hidpiImageType->id,
        'file' => ['en' => 'banners/test-hidpi.png'],
        'is_hidpi' => true,
        'image_width' => 1200, // 2x the base size
        'image_height' => 600,
    ]);

    expect($banner->images()->where('is_hidpi', true)->count())->toBe(1);
    expect($banner->images()->where('is_hidpi', false)->count())->toBe(0);

    // Mock the ImageService
    $mockImageService = $this->mock(\Eclipse\Cms\Services\ImageService::class);
    $mockImageService->shouldReceive('createRegularFromHidpi')
        ->with('banners/test-hidpi.png', 600, 300)
        ->andReturn('banners/test-hidpi_1x.png');

    // Manually trigger the observer (simulating what happens in the relation manager)
    $observer = app(\Eclipse\Cms\Observers\BannerObserver::class);
    $observer->updated($banner);

    // Should now have both HiDPI and regular images
    expect($banner->images()->where('is_hidpi', true)->count())->toBe(1);
    expect($banner->images()->where('is_hidpi', false)->count())->toBe(1);

    $regularImage = $banner->images()->where('is_hidpi', false)->first();
    expect($regularImage->type_id)->toBe($hidpiImageType->id);
    expect($regularImage->image_width)->toBe(600);
    expect($regularImage->image_height)->toBe(300);
    expect($regularImage->getTranslation('file', 'en'))->toBe('banners/test-hidpi_1x.png');
});

it('displays separate image columns for each image type', function () {
    $banner = Banner::factory()->create([
        'position_id' => $this->position->id,
    ]);

    $banner->images()->create([
        'type_id' => $this->position->imageTypes->first()->id,
        'file' => ['en' => 'banners/desktop-image.png'],
        'is_hidpi' => false,
        'image_width' => 1200,
        'image_height' => 400,
    ]);

    $banner->images()->create([
        'type_id' => $this->position->imageTypes->skip(1)->first()->id,
        'file' => ['en' => 'banners/mobile-image.png'],
        'is_hidpi' => true,
        'image_width' => 1200,
        'image_height' => 600,
    ]);

    $component = livewire(BannerRelationManager::class, [
        'ownerRecord' => $this->position,
        'pageClass' => 'Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\Pages\EditBannerPosition',
    ]);

    foreach ($this->position->imageTypes as $imageType) {
        $component->assertTableColumnExists("image_type_{$imageType->id}");
    }

    $component->assertCanSeeTableRecords([$banner]);
});

it('preserves existing images when editing banner without changing images', function () {
    $banner = Banner::factory()->create([
        'position_id' => $this->position->id,
    ]);

    $banner->images()->create([
        'type_id' => $this->position->imageTypes->first()->id,
        'file' => ['en' => 'banners/test-image.png'],
        'is_hidpi' => false,
        'image_width' => 1200,
        'image_height' => 600,
    ]);

    expect($banner->images()->count())->toBe(1, 'Should start with 1 image');

    livewire(BannerRelationManager::class, [
        'ownerRecord' => $this->position,
        'pageClass' => 'Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\Pages\EditBannerPosition',
    ])
        ->callTableAction('edit', $banner, data: [
            'name' => 'Updated Banner Name',
            'link' => $banner->link,
            'is_active' => $banner->is_active,
            'new_tab' => $banner->new_tab,
        ])
        ->assertHasNoTableActionErrors();

    $banner->refresh();
    expect($banner->images()->count())->toBe(1, 'Should still have 1 image after edit');
    expect($banner->getTranslation('name', 'en'))->toBe('Updated Banner Name');
});
