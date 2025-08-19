<?php

use Eclipse\Cms\Admin\Filament\Resources\MenuItemResource;
use Eclipse\Cms\Admin\Filament\Resources\MenuItemResource\Pages\CreateMenuItem;
use Eclipse\Cms\Admin\Filament\Resources\MenuItemResource\Pages\EditMenuItem;
use Eclipse\Cms\Admin\Filament\Resources\MenuItemResource\Pages\ListMenuItems;
use Eclipse\Cms\Enums\MenuItemType;
use Eclipse\Cms\Models\Menu;
use Eclipse\Cms\Models\Menu\Item;
use Eclipse\Cms\Models\Page;
use Eclipse\Cms\Models\Section;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->setUpSuperAdmin();
});

it('can render menu item index page', function () {
    $this->get(MenuItemResource::getUrl('index'))
        ->assertSuccessful();
});

it('can list menu items', function () {
    $menuItems = Item::factory()->count(10)->create();

    livewire(ListMenuItems::class)
        ->assertCanSeeTableRecords($menuItems);
});

it('can render menu item create page', function () {
    $this->get(MenuItemResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create custom url menu item', function () {
    $menu = Menu::factory()->create();

    livewire(CreateMenuItem::class)
        ->fillForm([
            'menu_id' => $menu->id,
            'label' => 'Test Link',
            'type' => 'CustomUrl',
            'custom_url' => 'https://example.com',
            'new_tab' => true,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Item::class, [
        'menu_id' => $menu->id,
        'label' => json_encode(['en' => 'Test Link']),
        'type' => 'CustomUrl',
        'custom_url' => 'https://example.com',
        'new_tab' => true,
        'is_active' => true,
    ]);
});

it('can create linkable menu item to page', function () {
    $menu = Menu::factory()->create();
    $page = Page::factory()->create();

    livewire(CreateMenuItem::class)
        ->fillForm([
            'menu_id' => $menu->id,
            'label' => 'Page Link',
            'type' => 'Linkable',
            'linkable_class' => Page::class,
            'linkable_id' => $page->id,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Item::class, [
        'menu_id' => $menu->id,
        'label' => json_encode(['en' => 'Page Link']),
        'type' => 'Linkable',
        'linkable_class' => Page::class,
        'linkable_id' => $page->id,
        'is_active' => true,
    ]);
});

it('can create linkable menu item to section', function () {
    $menu = Menu::factory()->create();
    $section = Section::factory()->create();

    livewire(CreateMenuItem::class)
        ->fillForm([
            'menu_id' => $menu->id,
            'label' => 'Section Link',
            'type' => 'Linkable',
            'linkable_class' => Section::class,
            'linkable_id' => $section->id,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Item::class, [
        'menu_id' => $menu->id,
        'label' => json_encode(['en' => 'Section Link']),
        'type' => 'Linkable',
        'linkable_class' => Section::class,
        'linkable_id' => $section->id,
        'is_active' => true,
    ]);
});

it('can create group menu item', function () {
    $menu = Menu::factory()->create();

    livewire(CreateMenuItem::class)
        ->fillForm([
            'menu_id' => $menu->id,
            'label' => 'Group Item',
            'type' => 'Group',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Item::class, [
        'menu_id' => $menu->id,
        'label' => json_encode(['en' => 'Group Item']),
        'type' => 'Group',
        'linkable_class' => null,
        'linkable_id' => null,
        'custom_url' => null,
        'is_active' => true,
    ]);
});

it('can validate menu item creation', function () {
    livewire(CreateMenuItem::class)
        ->fillForm([
            'label' => null,
            'menu_id' => null,
            'type' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'label' => 'required',
            'menu_id' => 'required',
            'type' => 'required',
        ]);
});

it('can render menu item edit page', function () {
    $menuItem = Item::factory()->customUrl()->create();

    $this->get(MenuItemResource::getUrl('edit', ['record' => $menuItem]))
        ->assertSuccessful();
});

it('can retrieve menu item data for editing', function () {
    $menuItem = Item::factory()->customUrl('https://test.com')->create();

    livewire(EditMenuItem::class, [
        'record' => $menuItem->getRouteKey(),
    ])
        ->assertFormSet([
            'menu_id' => $menuItem->menu_id,
            'label' => $menuItem->label,
            'type' => $menuItem->type->name,
            'custom_url' => $menuItem->custom_url,
            'is_active' => $menuItem->is_active,
        ]);
});

it('can save menu item', function () {
    $menuItem = Item::factory()->customUrl()->create();

    livewire(EditMenuItem::class, [
        'record' => $menuItem->getRouteKey(),
    ])
        ->fillForm([
            'label' => 'Updated Label',
            'custom_url' => 'https://updated.com',
            'is_active' => false,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($menuItem->refresh())
        ->label->toBe('Updated Label')
        ->custom_url->toBe('https://updated.com')
        ->is_active->toBe(false);
});

it('can delete menu item', function () {
    $menuItem = Item::factory()->create();

    livewire(ListMenuItems::class)
        ->callTableAction('delete', $menuItem);

    $this->assertSoftDeleted($menuItem);
});

it('can render tree sorting page', function () {
    $this->get(MenuItemResource::getUrl('sorting'))
        ->assertSuccessful();
});

it('can create nested menu items', function () {
    $menu = Menu::factory()->create();
    $parent = Item::factory()->group()->create(['menu_id' => $menu->id]);

    $child = Item::factory()->childOf($parent)->customUrl()->create();

    expect($child->parent_id)->toBe($parent->id)
        ->and($child->menu_id)->toBe($menu->id);
});

it('can filter menu items by menu', function () {
    $menu1 = Menu::factory()->create();
    $menu2 = Menu::factory()->create();

    $items1 = Item::factory()->count(3)->create(['menu_id' => $menu1->id]);
    $items2 = Item::factory()->count(2)->create(['menu_id' => $menu2->id]);

    livewire(ListMenuItems::class)
        ->filterTable('menu_id', $menu1->id)
        ->assertCanSeeTableRecords($items1)
        ->assertCanNotSeeTableRecords($items2);
});

it('can filter menu items by type', function () {
    $customUrlItems = Item::factory()->customUrl()->count(3)->create();
    $groupItems = Item::factory()->group()->count(2)->create();

    livewire(ListMenuItems::class)
        ->filterTable('type', ['CustomUrl'])
        ->assertCanSeeTableRecords($customUrlItems)
        ->assertCanNotSeeTableRecords($groupItems);
});

it('can search menu items', function () {
    $items = Item::factory()->count(10)->create();
    $searchItem = $items->first();

    livewire(ListMenuItems::class)
        ->searchTable($searchItem->label)
        ->assertCanSeeTableRecords([$searchItem]);
});

it('menu item getUrl method works correctly', function () {
    $pageItem = Item::factory()->linkableToPage()->create();
    $sectionItem = Item::factory()->linkableToSection()->create();
    $customUrlItem = Item::factory()->customUrl('https://example.com')->create();
    $groupItem = Item::factory()->group()->create();

    expect($customUrlItem->getUrl())->toBe('https://example.com');

    expect($groupItem->getUrl())->toBeNull();

    expect($pageItem->linkable)->not->toBeNull()
        ->and($pageItem->linkable)->toBeInstanceOf(Page::class)
        ->and($sectionItem->linkable)->not->toBeNull()
        ->and($sectionItem->linkable)->toBeInstanceOf(Section::class);

    expect($pageItem->type)->toBe(MenuItemType::Linkable)
        ->and($sectionItem->type)->toBe(MenuItemType::Linkable);
});

test('unauthorized access can be prevented', function () {
    $this->setUpUserWithoutPermissions();

    livewire(ListMenuItems::class)
        ->assertForbidden();
});

test('user with create permission can create menu items', function () {
    $this->setUpUserWithPermissions(['view_any_menu::item', 'create_menu::item']);

    livewire(CreateMenuItem::class)
        ->assertSuccessful();
});

test('user with update permission can edit menu items', function () {
    $this->setUpUserWithPermissions(['view_any_menu::item', 'view_menu::item', 'update_menu::item']);
    $menuItem = Item::factory()->create();

    livewire(EditMenuItem::class, [
        'record' => $menuItem->getRouteKey(),
    ])
        ->assertSuccessful();
});

test('user with delete permission can delete menu items', function () {
    $this->setUpUserWithPermissions(['view_any_menu::item', 'view_menu::item', 'delete_menu::item']);
    $menuItem = Item::factory()->create();

    $itemExists = Item::where('id', $menuItem->id)->exists();
    expect($itemExists)->toBeTrue();

    $menuItem->delete();

    expect($menuItem->fresh()->trashed())->toBeTrue();
});
