<?php

use Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\Pages\EditBannerPosition;
use Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\RelationManagers\BannersRelationManager;
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

it('can render banners relation manager', function () {
    $banner = Banner::factory()->create([
        'position_id' => $this->position->id,
        'name' => 'Test Banner',
        'link' => 'https://example.com',
        'is_active' => true,
        'new_tab' => false,
        'sort' => 1,
    ]);

    livewire(BannersRelationManager::class, [
        'ownerRecord' => $this->position,
        'pageClass' => EditBannerPosition::class,
    ])
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$banner]);
});

it('generates correct repeater items for hidpi types', function () {
    $component = livewire(BannersRelationManager::class, [
        'ownerRecord' => $this->position,
        'pageClass' => EditBannerPosition::class,
    ]);

    $component->mountTableAction('create');

    expect($this->position->imageTypes)->toHaveCount(2);
    expect($this->position->imageTypes->where('is_hidpi', true))->toHaveCount(1);
});

it('validates required images on banner creation', function () {
    livewire(BannersRelationManager::class, [
        'ownerRecord' => $this->position,
        'pageClass' => EditBannerPosition::class,
    ])
        ->callTableAction('create', data: [
            'name' => 'Test Banner',
            'link' => 'https://example.com',
            'is_active' => true,
            'new_tab' => false,
            'images' => [
                ['type_id' => $this->position->imageTypes->first()->id, 'is_hidpi' => false, 'file' => null],
                ['type_id' => $this->position->imageTypes->last()->id, 'is_hidpi' => false, 'file' => null],
                ['type_id' => $this->position->imageTypes->last()->id, 'is_hidpi' => true, 'file' => null],
            ],
        ])
        ->assertHasTableActionErrors();

    expect(Banner::where('name', 'Test Banner')->first())->toBeNull();
});

it('can view banner', function () {
    $banner = Banner::factory()->create([
        'position_id' => $this->position->id,
        'name' => 'Test Banner',
        'link' => 'https://example.com',
        'is_active' => true,
        'new_tab' => true,
        'sort' => 1,
    ]);

    $banner->images()->create([
        'type_id' => $this->position->imageTypes->first()->id,
        'file' => ['en' => 'test.png'],
        'image_width' => 1200,
        'image_height' => 400,
        'is_hidpi' => false,
    ]);

    livewire(BannersRelationManager::class, [
        'ownerRecord' => $this->position,
        'pageClass' => EditBannerPosition::class,
    ])
        ->mountTableAction('view', $banner)
        ->assertTableActionExists('view');
});

it('can delete banner and cleanup images', function () {
    $banner = Banner::factory()->create([
        'position_id' => $this->position->id,
        'name' => 'Test Banner',
        'is_active' => true,
        'new_tab' => false,
        'sort' => 1,
    ]);

    $image = $banner->images()->create([
        'type_id' => $this->position->imageTypes->first()->id,
        'file' => ['en' => 'test.jpg'],
        'image_width' => 1200,
        'image_height' => 400,
        'is_hidpi' => false,
    ]);

    livewire(BannersRelationManager::class, [
        'ownerRecord' => $this->position,
        'pageClass' => EditBannerPosition::class,
    ])
        ->callTableAction('delete', $banner);

    $this->assertSoftDeleted($banner);
    expect($image->fresh())->toBeNull();
});

it('can sort banners automatically', function () {
    Banner::factory()->create([
        'position_id' => $this->position->id,
        'name' => 'Existing Banner',
        'sort' => 1,
    ]);

    $bannersCount = $this->position->banners()->count();

    expect($bannersCount)->toBe(1);

    $maxSort = $this->position->banners()->max('sort');
    expect($maxSort)->toBe(1);
});

it('can search banners', function () {
    $banners = Banner::factory()->count(3)->create([
        'position_id' => $this->position->id,
    ]);

    $firstBanner = $banners->first();
    $bannerName = is_array($firstBanner->name) ? $firstBanner->name['en'] ?? $firstBanner->name : $firstBanner->name;

    livewire(BannersRelationManager::class, [
        'ownerRecord' => $this->position,
        'pageClass' => EditBannerPosition::class,
    ])
        ->searchTable($bannerName)
        ->assertCanSeeTableRecords($banners->take(1));
});

it('can filter banners by active status', function () {
    $activeBanner = Banner::factory()->create([
        'position_id' => $this->position->id,
        'name' => 'Active Banner',
        'is_active' => true,
    ]);

    $inactiveBanner = Banner::factory()->create([
        'position_id' => $this->position->id,
        'name' => 'Inactive Banner',
        'is_active' => false,
    ]);

    livewire(BannersRelationManager::class, [
        'ownerRecord' => $this->position,
        'pageClass' => EditBannerPosition::class,
    ])
        ->assertCanSeeTableRecords([$activeBanner, $inactiveBanner])
        ->filterTable('is_active', true)
        ->assertCanSeeTableRecords([$activeBanner])
        ->assertCanNotSeeTableRecords([$inactiveBanner]);
});

it('can filter banners by new tab setting', function () {
    $newTabBanner = Banner::factory()->create([
        'position_id' => $this->position->id,
        'name' => 'New Tab Banner',
        'new_tab' => true,
    ]);

    $sameTabBanner = Banner::factory()->create([
        'position_id' => $this->position->id,
        'name' => 'Same Tab Banner',
        'new_tab' => false,
    ]);

    livewire(BannersRelationManager::class, [
        'ownerRecord' => $this->position,
        'pageClass' => EditBannerPosition::class,
    ])
        ->assertCanSeeTableRecords([$newTabBanner, $sameTabBanner])
        ->filterTable('new_tab', true)
        ->assertCanSeeTableRecords([$newTabBanner])
        ->assertCanNotSeeTableRecords([$sameTabBanner]);
});
