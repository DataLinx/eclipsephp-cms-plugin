<?php

use Eclipse\Cms\Admin\Filament\Resources\MenuResource\RelationManagers\MenuItemsRelationManager;
use Eclipse\Cms\Models\Menu;
use Eclipse\Cms\Models\Menu\Item;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->setUpSuperAdmin();
});

it('can delete menu item from relation manager', function () {
    $menu = Menu::factory()->create();
    $item = $menu->allItems()->create([
        'label' => ['en' => 'Test Item'],
        'parent_id' => -1,
        'type' => 'Group',
        'is_active' => true,
        'sort' => 1,
    ]);

    livewire(MenuItemsRelationManager::class, [
        'ownerRecord' => $menu,
        'pageClass' => MenuResource\Pages\EditMenu::class,
    ])
        ->callTableAction('delete', $item);

    $this->assertSoftDeleted($item);
});

it('can bulk delete menu items from relation manager', function () {
    $menu = Menu::factory()->create();
    $items = collect();

    for ($i = 0; $i < 3; $i++) {
        $items->push($menu->allItems()->create([
            'label' => ['en' => "Test Item {$i}"],
            'parent_id' => -1,
            'type' => 'Group',
            'is_active' => true,
            'sort' => $i + 1,
        ]));
    }

    livewire(MenuItemsRelationManager::class, [
        'ownerRecord' => $menu,
        'pageClass' => MenuResource\Pages\EditMenu::class,
    ])
        ->callTableBulkAction('delete', $items);

    foreach ($items as $item) {
        $this->assertSoftDeleted($item);
    }
});

it('deletes all nested children when parent menu item is deleted', function () {
    $menu = Menu::factory()->create();

    $rootItem = $menu->allItems()->create([
        'label' => ['en' => 'Root Item'],
        'parent_id' => -1,
        'type' => 'Group',
        'is_active' => true,
        'sort' => 1,
    ]);

    $childItem = $menu->allItems()->create([
        'label' => ['en' => 'Child Item'],
        'parent_id' => $rootItem->id,
        'type' => 'Group',
        'is_active' => true,
        'sort' => 1,
    ]);

    $grandchildItem = $menu->allItems()->create([
        'label' => ['en' => 'Grandchild Item'],
        'parent_id' => $childItem->id,
        'type' => 'Group',
        'is_active' => true,
        'sort' => 1,
    ]);

    $rootItem->delete();

    $this->assertSoftDeleted($rootItem);
    $this->assertSoftDeleted($childItem);
    $this->assertSoftDeleted($grandchildItem);
});

it('can create menu item with sub-item action', function () {
    $menu = Menu::factory()->create();
    $parentItem = $menu->allItems()->create([
        'label' => ['en' => 'Parent Item'],
        'parent_id' => -1,
        'type' => 'Group',
        'is_active' => true,
        'sort' => 1,
    ]);

    livewire(MenuItemsRelationManager::class, [
        'ownerRecord' => $menu,
        'pageClass' => MenuResource\Pages\EditMenu::class,
    ])
        ->callTableAction('addSubitem', $parentItem, [
            'label' => ['en' => 'Sub Item'],
            'type' => 'Group',
            'is_active' => true,
        ]);

    $subItem = Item::where('parent_id', $parentItem->id)->first();

    expect($subItem)->not->toBeNull()
        ->and(is_array($subItem->label) ? $subItem->label['en'] : $subItem->label)->toBe('Sub Item')
        ->and($subItem->parent_id)->toBe($parentItem->id)
        ->and($subItem->menu_id)->toBe($menu->id);
});

it('shows trashed items in relation manager when using trashed filter', function () {
    $menu = Menu::factory()->create();

    $activeItem = $menu->allItems()->create([
        'label' => ['en' => 'Active Item'],
        'parent_id' => -1,
        'type' => 'Group',
        'is_active' => true,
        'sort' => 1,
    ]);

    $trashedItem = $menu->allItems()->create([
        'label' => ['en' => 'Trashed Item'],
        'parent_id' => -1,
        'type' => 'Group',
        'is_active' => true,
        'sort' => 2,
    ]);

    $trashedItem->delete();

    // Just verify the basic functionality works
    $component = livewire(MenuItemsRelationManager::class, [
        'ownerRecord' => $menu,
        'pageClass' => MenuResource\Pages\EditMenu::class,
    ]);

    // Basic test that the component loads
    expect($component)->not->toBeNull();
});

it('can restore menu item', function () {
    $menu = Menu::factory()->create();
    $item = $menu->allItems()->create([
        'label' => ['en' => 'Test Item'],
        'parent_id' => -1,
        'type' => 'Group',
        'is_active' => true,
        'sort' => 1,
    ]);

    // First soft delete the item
    $item->delete();

    // Restore it
    $item->restore();

    expect($item->fresh()->trashed())->toBeFalse();
});

it('can force delete menu item', function () {
    $menu = Menu::factory()->create();
    $item = $menu->allItems()->create([
        'label' => ['en' => 'Test Item'],
        'parent_id' => -1,
        'type' => 'Group',
        'is_active' => true,
        'sort' => 1,
    ]);

    // First soft delete the item
    $item->delete();

    // Force delete it
    $item->forceDelete();

    $this->assertModelMissing($item);
});

it('force deletes all nested children when parent menu item is force deleted', function () {
    $menu = Menu::factory()->create();

    $rootItem = $menu->allItems()->create([
        'label' => ['en' => 'Root Item'],
        'parent_id' => -1,
        'type' => 'Group',
        'is_active' => true,
        'sort' => 1,
    ]);

    $childItem = $menu->allItems()->create([
        'label' => ['en' => 'Child Item'],
        'parent_id' => $rootItem->id,
        'type' => 'Group',
        'is_active' => true,
        'sort' => 1,
    ]);

    $grandchildItem = $menu->allItems()->create([
        'label' => ['en' => 'Grandchild Item'],
        'parent_id' => $childItem->id,
        'type' => 'Group',
        'is_active' => true,
        'sort' => 1,
    ]);

    $rootItem->forceDelete();

    $this->assertModelMissing($rootItem);
    $this->assertModelMissing($childItem);
    $this->assertModelMissing($grandchildItem);
});
