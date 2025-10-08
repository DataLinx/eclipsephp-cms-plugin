<?php

use Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource;
use Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\Pages\CreateBannerPosition;
use Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\Pages\EditBannerPosition;
use Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\Pages\ListBannerPositions;
use Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\Pages\ViewBannerPosition;
use Eclipse\Cms\Models\Banner;
use Eclipse\Cms\Models\Banner\Position;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->setUpSuperAdmin();
});

it('can render position index page', function () {
    $this->get(BannerPositionResource::getUrl('index'))
        ->assertSuccessful();
});

it('can list positions', function () {
    $positions = Position::factory()->count(3)->create();

    livewire(ListBannerPositions::class)
        ->assertCanSeeTableRecords($positions);
});

it('can render position create page', function () {
    $this->get(BannerPositionResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create position', function () {
    $newData = Position::factory()->make();

    livewire(CreateBannerPosition::class)
        ->fillForm([
            'name' => $newData->name,
            'code' => $newData->code,
            'imageTypes' => [],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Position::class, [
        'code' => $newData->code,
    ]);

    $position = Position::where('code', $newData->code)->first();
    expect($position)->toBeInstanceOf(Position::class)
        ->and($position->name)->toBe($newData->name);
});

it('can create position without image types', function () {
    $newData = Position::factory()->make([
        'name' => 'Header Banner',
        'code' => 'header',
    ]);

    livewire(CreateBannerPosition::class)
        ->fillForm([
            'name' => $newData->name,
            'code' => $newData->code,
            'imageTypes' => [],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $position = Position::where('code', 'header')->with('imageTypes')->first();
    expect($position)->toBeInstanceOf(Position::class);
    expect($position->imageTypes)->toHaveCount(0);
});

it('can render position edit page', function () {
    $position = Position::factory()->create();

    $this->get(BannerPositionResource::getUrl('edit', ['record' => $position]))
        ->assertSuccessful();
});

it('can retrieve position data for editing', function () {
    $position = Position::factory()->create();

    livewire(EditBannerPosition::class, [
        'record' => $position->getRouteKey(),
    ])
        ->assertFormSet([
            'name' => $position->name,
            'code' => $position->code,
        ]);
});

it('can save position', function () {
    $position = Position::factory()->create();
    $newData = Position::factory()->make();

    livewire(EditBannerPosition::class, [
        'record' => $position->getRouteKey(),
    ])
        ->fillForm([
            'name' => $newData->name,
            'code' => $newData->code,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($position->refresh())
        ->name->toBe($newData->name)
        ->code->toBe($newData->code);
});

it('can delete position', function () {
    $position = Position::factory()->create();

    livewire(ListBannerPositions::class)
        ->callTableAction('delete', $position);

    $this->assertSoftDeleted($position);
});

it('can validate position creation', function () {
    livewire(CreateBannerPosition::class)
        ->fillForm([
            'name' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

it('can filter positions', function () {
    $positions = Position::factory()->count(5)->create();

    livewire(ListBannerPositions::class)
        ->assertCanSeeTableRecords($positions)
        ->assertTableFilterExists('trashed');
});

it('can render position view page', function () {
    $position = Position::factory()->create();

    $this->get(BannerPositionResource::getUrl('view', ['record' => $position]))
        ->assertSuccessful();
});

it('can view position and manage banners', function () {
    $position = Position::factory()->create();

    livewire(ViewBannerPosition::class, [
        'record' => $position->getRouteKey(),
    ])
        ->assertSuccessful();
});

it('can access edit from view page', function () {
    $this->setUpSuperAdmin();

    $position = Position::factory()->create();

    $component = livewire(ViewBannerPosition::class, [
        'record' => $position->getRouteKey(),
    ])
        ->assertSuccessful();

    $editUrl = BannerPositionResource::getUrl('edit', ['record' => $position]);
    expect($editUrl)->toContain('edit');
    expect($editUrl)->toContain((string) $position->getRouteKey());
});

it('deletes all related banners and image files when position is deleted', function () {
    Storage::fake();

    Storage::put('banners/banner1-desktop.png', 'fake-image-content');
    Storage::put('banners/banner1-mobile@2x.png', 'fake-hidpi-content');
    Storage::put('banners/banner1-mobile@2x_1x.png', 'fake-regular-content');

    $position = Position::factory()->create();
    $banner = Banner::factory()->create(['position_id' => $position->id]);

    $banner->images()->createMany([
        [
            'type_id' => 1,
            'file' => ['en' => 'banners/banner1-desktop.png'],
            'is_hidpi' => false,
            'image_width' => 1200,
            'image_height' => 400,
        ],
        [
            'type_id' => 2,
            'file' => ['en' => 'banners/banner1-mobile@2x.png'],
            'is_hidpi' => true,
            'image_width' => 1600,
            'image_height' => 800,
        ],
        [
            'type_id' => 2,
            'file' => ['en' => 'banners/banner1-mobile@2x_1x.png'],
            'is_hidpi' => false,
            'image_width' => 800,
            'image_height' => 400,
        ],
    ]);

    expect($banner->images()->get())->toHaveCount(3);
    expect(Storage::exists('banners/banner1-desktop.png'))->toBeTrue();
    expect(Storage::exists('banners/banner1-mobile@2x.png'))->toBeTrue();
    expect(Storage::exists('banners/banner1-mobile@2x_1x.png'))->toBeTrue();

    $position->delete();

    $this->assertSoftDeleted($position);
    $this->assertSoftDeleted($banner);
    expect(Banner::withTrashed()->find($banner->id)->images()->get())->toHaveCount(0);
    expect(Storage::exists('banners/banner1-desktop.png'))->toBeFalse();
    expect(Storage::exists('banners/banner1-mobile@2x.png'))->toBeFalse();
    expect(Storage::exists('banners/banner1-mobile@2x_1x.png'))->toBeFalse();
});
