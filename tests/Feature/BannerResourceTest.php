<?php

use Eclipse\Cms\Admin\Filament\Resources\BannerResource;
use Eclipse\Cms\Admin\Filament\Resources\BannerResource\Pages\CreateBanner;
use Eclipse\Cms\Admin\Filament\Resources\BannerResource\Pages\EditBanner;
use Eclipse\Cms\Admin\Filament\Resources\BannerResource\Pages\ListBanners;
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

it('can render banner index page', function () {
    $this->get(BannerResource::getUrl('index'))
        ->assertSuccessful();
});

it('can list banners', function () {
    $banners = Banner::factory()->count(3)->create([
        'position_id' => $this->position->id,
    ]);

    livewire(ListBanners::class)
        ->assertCanSeeTableRecords($banners);
});

it('can render banner create page', function () {
    $this->get(BannerResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create banner', function () {
    $newData = Banner::factory()->make([
        'position_id' => $this->position->id,
    ]);

    livewire(CreateBanner::class)
        ->fillForm([
            'position_id' => $newData->position_id,
            'name' => $newData->name,
            'link' => $newData->link,
            'is_active' => $newData->is_active,
            'new_tab' => $newData->new_tab,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $banner = Banner::where('position_id', $newData->position_id)->first();
    expect($banner)->not->toBeNull()
        ->and($banner->getTranslation('name', 'en'))->toBe($newData->name);
});

it('can render banner edit page', function () {
    $banner = Banner::factory()->create([
        'position_id' => $this->position->id,
    ]);

    $this->get(BannerResource::getUrl('edit', ['record' => $banner]))
        ->assertSuccessful();
});

it('can retrieve banner data for editing', function () {
    $banner = Banner::factory()->create([
        'position_id' => $this->position->id,
    ]);

    livewire(EditBanner::class, [
        'record' => $banner->getRouteKey(),
    ])
        ->assertFormSet([
            'position_id' => $banner->position_id,
            'name' => $banner->name,
            'link' => $banner->link,
            'is_active' => $banner->is_active,
            'new_tab' => $banner->new_tab,
        ]);
});

it('can save banner', function () {
    $banner = Banner::factory()->create([
        'position_id' => $this->position->id,
    ]);
    $newData = Banner::factory()->make([
        'position_id' => $this->position->id,
    ]);

    livewire(EditBanner::class, [
        'record' => $banner->getRouteKey(),
    ])
        ->fillForm([
            'position_id' => $newData->position_id,
            'name' => $newData->name,
            'link' => $newData->link,
            'is_active' => $newData->is_active,
            'new_tab' => $newData->new_tab,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($banner->refresh())
        ->name->toBe($newData->name)
        ->link->toBe($newData->link)
        ->is_active->toBe($newData->is_active)
        ->new_tab->toBe($newData->new_tab);
});

it('can delete banner', function () {
    $banner = Banner::factory()->create([
        'position_id' => $this->position->id,
    ]);

    livewire(ListBanners::class)
        ->callTableAction('delete', $banner);

    $this->assertSoftDeleted($banner);
});

it('can validate banner creation', function () {
    livewire(CreateBanner::class)
        ->fillForm([
            'name' => null,
            'position_id' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required', 'position_id' => 'required']);
});

it('can filter banners by position', function () {
    $position2 = Position::factory()->create(['name' => 'Sidebar']);

    $headerBanners = Banner::factory()->count(2)->create([
        'position_id' => $this->position->id,
    ]);

    $sidebarBanners = Banner::factory()->count(2)->create([
        'position_id' => $position2->id,
    ]);

    livewire(ListBanners::class)
        ->assertCanSeeTableRecords([...$headerBanners, ...$sidebarBanners])
        ->filterTable('position_id', $this->position->id)
        ->assertCanSeeTableRecords($headerBanners)
        ->assertCanNotSeeTableRecords($sidebarBanners);
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

    livewire(ListBanners::class)
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

    livewire(ListBanners::class)
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

    $newData = Banner::factory()->make([
        'position_id' => $this->position->id,
    ]);

    livewire(CreateBanner::class)
        ->fillForm([
            'position_id' => $newData->position_id,
            'name' => $newData->name,
            'link' => $newData->link,
            'is_active' => $newData->is_active,
            'new_tab' => $newData->new_tab,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $newBanner = Banner::where('position_id', $newData->position_id)
        ->where('sort', '>', 5)
        ->first();
    expect($newBanner)->not->toBeNull()
        ->and($newBanner->sort)->toBe(6);
});
