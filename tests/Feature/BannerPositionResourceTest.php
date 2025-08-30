<?php

use Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource;
use Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\Pages\CreateBannerPosition;
use Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\Pages\EditBannerPosition;
use Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\Pages\ListBannerPositions;
use Eclipse\Cms\Models\Banner\Position;

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
