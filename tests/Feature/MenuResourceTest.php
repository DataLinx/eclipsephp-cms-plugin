<?php

use Eclipse\Cms\Admin\Filament\Resources\MenuResource;
use Eclipse\Cms\Admin\Filament\Resources\MenuResource\Pages\CreateMenu;
use Eclipse\Cms\Admin\Filament\Resources\MenuResource\Pages\EditMenu;
use Eclipse\Cms\Admin\Filament\Resources\MenuResource\Pages\ListMenus;
use Eclipse\Cms\Models\Menu;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->setUpSuperAdmin();
});

it('can render menu index page', function () {
    $this->get(MenuResource::getUrl('index'))
        ->assertSuccessful();
});

it('can list menus', function () {
    $menus = Menu::factory()->count(10)->create();

    livewire(ListMenus::class)
        ->assertCanSeeTableRecords($menus);
});

it('can render menu create page', function () {
    $this->get(MenuResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create menu', function () {
    $newData = Menu::factory()->make();

    livewire(CreateMenu::class)
        ->fillForm([
            'title' => $newData->title,
            'code' => $newData->code,
            'is_active' => $newData->is_active,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Menu::class, [
        'code' => $newData->code,
        'is_active' => $newData->is_active,
    ]);

    $menu = Menu::where('code', $newData->code)->first();
    expect($menu)->not->toBeNull()
        ->and($menu->title)->toBe($newData->title);
});

it('can validate menu creation', function () {
    livewire(CreateMenu::class)
        ->fillForm([
            'title' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['title' => 'required']);
});

it('can render menu edit page', function () {
    $menu = Menu::factory()->create();

    $this->get(MenuResource::getUrl('edit', ['record' => $menu]))
        ->assertSuccessful();
});

it('can retrieve menu data for editing', function () {
    $menu = Menu::factory()->create();

    livewire(EditMenu::class, [
        'record' => $menu->getRouteKey(),
    ])
        ->assertFormSet([
            'title' => $menu->title,
            'code' => $menu->code,
            'is_active' => $menu->is_active,
        ]);
});

it('can save menu', function () {
    $menu = Menu::factory()->create();
    $newData = Menu::factory()->make();

    livewire(EditMenu::class, [
        'record' => $menu->getRouteKey(),
    ])
        ->fillForm([
            'title' => $newData->title,
            'code' => $newData->code,
            'is_active' => $newData->is_active,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($menu->refresh())
        ->title->toBe($newData->title)
        ->code->toBe($newData->code)
        ->is_active->toBe($newData->is_active);
});

it('can validate menu editing', function () {
    $menu = Menu::factory()->create();

    livewire(EditMenu::class, [
        'record' => $menu->getRouteKey(),
    ])
        ->fillForm([
            'title' => null,
        ])
        ->call('save')
        ->assertHasFormErrors(['title' => 'required']);
});

it('can delete menu', function () {
    $menu = Menu::factory()->create();

    livewire(ListMenus::class)
        ->callTableAction('delete', $menu);

    $this->assertSoftDeleted($menu);
});

it('can bulk delete menus', function () {
    $menus = Menu::factory()->count(10)->create();

    livewire(ListMenus::class)
        ->callTableBulkAction('delete', $menus);

    foreach ($menus as $menu) {
        $this->assertSoftDeleted($menu);
    }
});

it('can search menus', function () {
    $menus = Menu::factory()->count(10)->create();

    $title = $menus->first()->title['en'] ?? $menus->first()->title;

    livewire(ListMenus::class)
        ->searchTable($title)
        ->assertCanSeeTableRecords($menus->take(1))
        ->assertCanNotSeeTableRecords($menus->skip(1));
});

it('can sort menus', function () {
    $menus = Menu::factory()->count(10)->create();

    livewire(ListMenus::class)
        ->sortTable('title')
        ->assertCanSeeTableRecords($menus->sortBy('title'), inOrder: true)
        ->sortTable('title', 'desc')
        ->assertCanSeeTableRecords($menus->sortByDesc('title'), inOrder: true);
});

it('can filter menus by active status', function () {
    $activeMenus = Menu::factory()->active()->count(5)->create();
    $inactiveMenus = Menu::factory()->inactive()->count(5)->create();

    livewire(ListMenus::class)
        ->assertCanSeeTableRecords($activeMenus)
        ->assertCanSeeTableRecords($inactiveMenus);
});

test('unauthorized access can be prevented', function () {
    $this->setUpUserWithoutPermissions();

    livewire(ListMenus::class)
        ->assertForbidden();
});

test('user with create permission can create menus', function () {
    $this->setUpUserWithPermissions(['view_any_menu', 'create_menu']);

    livewire(CreateMenu::class)
        ->assertSuccessful();
});

test('user with update permission can edit menus', function () {
    $this->setUpUserWithPermissions(['view_any_menu', 'view_menu', 'update_menu']);
    $menu = Menu::factory()->create();

    livewire(EditMenu::class, [
        'record' => $menu->getRouteKey(),
    ])
        ->assertSuccessful();
});

test('user with delete permission can delete menus', function () {
    $this->setUpUserWithPermissions(['view_any_menu', 'view_menu', 'delete_menu']);
    $menu = Menu::factory()->create();

    $menuExists = Menu::where('id', $menu->id)->exists();
    expect($menuExists)->toBeTrue();

    $menu->delete();

    expect($menu->fresh()->trashed())->toBeTrue();
});
