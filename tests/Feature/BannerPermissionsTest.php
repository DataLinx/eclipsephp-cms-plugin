<?php

use Eclipse\Cms\Admin\Filament\Resources\BannerResource;
use Eclipse\Cms\Admin\Filament\Resources\BannerResource\Pages\CreateBanner;
use Eclipse\Cms\Admin\Filament\Resources\BannerResource\Pages\EditBanner;
use Eclipse\Cms\Admin\Filament\Resources\BannerResource\Pages\ListBanners;
use Eclipse\Cms\Models\Banner;
use Eclipse\Cms\Models\Banner\Position;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->position = Position::factory()->create();
});

it('denies access to banner index without view_any_banner permission', function () {
    $this->setUpUserWithoutPermissions();

    $this->get(BannerResource::getUrl('index'))
        ->assertForbidden();
});

it('allows access to banner index with view_any_banner permission', function () {
    $this->setUpUserWithPermissions(['view_any_banner']);

    $this->get(BannerResource::getUrl('index'))
        ->assertSuccessful();
});

it('denies banner creation without create_banner permission', function () {
    $this->setUpUserWithPermissions(['view_any_banner']);

    $this->get(BannerResource::getUrl('create'))
        ->assertForbidden();
});

it('allows banner creation with create_banner permission', function () {
    $this->setUpUserWithPermissions(['view_any_banner', 'create_banner']);

    livewire(CreateBanner::class)
        ->fillForm([
            'position_id' => $this->position->id,
            'name' => 'Test Banner',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $banner = Banner::where('position_id', $this->position->id)->first();
    expect($banner)->not->toBeNull()
        ->and($banner->getTranslation('name', 'en'))->toBe('Test Banner');
});

it('denies banner editing without update_banner permission', function () {
    $this->setUpUserWithPermissions(['view_any_banner', 'view_banner']);

    $banner = Banner::factory()->create([
        'position_id' => $this->position->id,
    ]);

    $this->get(BannerResource::getUrl('edit', ['record' => $banner]))
        ->assertForbidden();
});

it('allows banner editing with update_banner permission', function () {
    $this->setUpUserWithPermissions(['view_any_banner', 'view_banner', 'update_banner']);

    $banner = Banner::factory()->create([
        'position_id' => $this->position->id,
    ]);

    livewire(EditBanner::class, [
        'record' => $banner->getRouteKey(),
    ])
        ->fillForm([
            'name' => 'Updated Banner',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($banner->refresh()->name)->toBe('Updated Banner');
});

it('denies banner deletion without delete_banner permission', function () {
    $this->setUpUserWithPermissions(['view_any_banner']);

    $banner = Banner::factory()->create([
        'position_id' => $this->position->id,
    ]);

    livewire(ListBanners::class)
        ->assertTableActionHidden('delete', $banner);
});

it('allows banner deletion with delete_banner permission', function () {
    $this->setUpUserWithPermissions(['view_any_banner', 'delete_banner']);

    $banner = Banner::factory()->create([
        'position_id' => $this->position->id,
    ]);

    livewire(ListBanners::class)
        ->callTableAction('delete', $banner);

    $this->assertSoftDeleted($banner);
});

it('denies banner view without view_banner permission', function () {
    $this->setUpUserWithPermissions(['view_any_banner']);

    $banner = Banner::factory()->create([
        'position_id' => $this->position->id,
    ]);

    $this->get(BannerResource::getUrl('view', ['record' => $banner]))
        ->assertForbidden();
});

it('allows banner view with view_banner permission', function () {
    $this->setUpUserWithPermissions(['view_any_banner', 'view_banner']);

    $banner = Banner::factory()->create([
        'position_id' => $this->position->id,
    ]);

    $this->get(BannerResource::getUrl('view', ['record' => $banner]))
        ->assertSuccessful();
});
