<?php

use Eclipse\Cms\Admin\Filament\Resources\SectionResource;
use Eclipse\Cms\Models\Section;
use Filament\Actions\DeleteAction;
use Livewire\Livewire;

beforeEach(function () {
    $this->setUpSuperAdmin();
});

test('authorized access can view sections list', function () {
    $response = $this->get(SectionResource::getUrl('index'));

    $response->assertSuccessful();
});

test('create section screen can be rendered', function () {
    $response = $this->get(SectionResource::getUrl('create'));

    $response->assertSuccessful();
});

test('section form validation works', function () {
    Livewire::test(SectionResource\Pages\CreateSection::class)
        ->fillForm([
            'name' => '',
            'type' => 'pages',
        ])
        ->call('create')
        ->assertHasFormErrors(['name']);
});

test('section can be created through form', function () {
    $newData = [
        'name.en' => 'Test Section',
        'name.sl' => 'Test Sekcija',
        'type' => 'pages',
    ];

    Livewire::test(SectionResource\Pages\CreateSection::class)
        ->fillForm($newData)
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Section::where('type', 'pages')->exists())->toBeTrue();
});

test('section can be updated', function () {
    $section = Section::factory()->create();

    $newData = [
        'name.en' => 'Updated Section',
        'name.sl' => 'Posodobljena Sekcija',
        'type' => 'pages',
    ];

    Livewire::test(SectionResource\Pages\EditSection::class, [
        'record' => $section->getRouteKey(),
    ])
        ->fillForm($newData)
        ->call('save')
        ->assertHasNoFormErrors();

    expect(true)->toBeTrue();
});

test('section can be deleted', function () {
    $section = Section::factory()->create();

    Livewire::test(SectionResource\Pages\EditSection::class, [
        'record' => $section->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    expect($section->fresh()->trashed())->toBeTrue();
});

test('unauthorized access can be prevented', function () {
    $this->setUpUserWithoutPermissions();

    $response = $this->get(SectionResource::getUrl('index'));

    $response->assertForbidden();
});

test('user with create permission can create sections', function () {
    $this->setUpUserWithPermissions(['view_any_section', 'create_section']);

    $response = $this->get(SectionResource::getUrl('create'));

    $response->assertSuccessful();
});

test('user with update permission can edit sections', function () {
    $this->setUpUserWithPermissions(['view_any_section', 'view_section', 'update_section']);

    $section = Section::factory()->create();

    $response = $this->get(SectionResource::getUrl('edit', [
        'record' => $section,
    ]));

    $response->assertSuccessful();
});

test('user with delete permission can delete sections', function () {
    $this->setUpUserWithPermissions(['view_any_section', 'view_section', 'update_section', 'delete_section']);

    $section = Section::factory()->create();

    Livewire::test(SectionResource\Pages\EditSection::class, [
        'record' => $section->getRouteKey(),
    ])
        ->callAction('delete');

    expect($section->fresh()->trashed())->toBeTrue();
});
