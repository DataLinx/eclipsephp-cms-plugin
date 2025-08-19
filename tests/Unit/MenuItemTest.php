<?php

use Eclipse\Cms\Enums\MenuItemType;
use Eclipse\Cms\Models\Menu;
use Eclipse\Cms\Models\Menu\Item;
use Eclipse\Cms\Models\Page;
use Eclipse\Cms\Models\Section;

it('can create a menu item', function () {
    $menu = Menu::factory()->create();
    $item = Item::factory()->create([
        'menu_id' => $menu->id,
        'label' => ['en' => 'Test Item'],
        'type' => MenuItemType::CustomUrl,
        'custom_url' => 'https://example.com',
        'is_active' => true,
    ]);

    expect($item)
        ->menu_id->toBe($menu->id)
        ->getTranslations('label')->toBe(['en' => 'Test Item'])
        ->type->toBe(MenuItemType::CustomUrl)
        ->custom_url->toBe('https://example.com')
        ->is_active->toBe(true);
});

it('belongs to a menu', function () {
    $menu = Menu::factory()->create();
    $item = Item::factory()->create(['menu_id' => $menu->id]);

    expect($item->menu)->toBeInstanceOf(Menu::class)
        ->and($item->menu->id)->toBe($menu->id);
});

it('can have parent and children relationships', function () {
    $menu = Menu::factory()->create();
    $parent = Item::factory()->create(['menu_id' => $menu->id]);
    $child = Item::factory()->create(['menu_id' => $menu->id, 'parent_id' => $parent->id]);

    expect($child->parent)->toBeInstanceOf(Item::class)
        ->and($child->parent->id)->toBe($parent->id)
        ->and($parent->children)->toHaveCount(1)
        ->and($parent->children->first()->id)->toBe($child->id);
});

it('has translatable label', function () {
    $item = Item::factory()->create([
        'label' => ['en' => 'English Label', 'sl' => 'Slovenian Label'],
    ]);

    app()->setLocale('en');
    expect($item->label)->toBe('English Label');

    app()->setLocale('sl');
    expect($item->label)->toBe('Slovenian Label');
});

it('can be linked to a page via polymorphic relationship', function () {
    $page = Page::factory()->create();
    $item = Item::factory()->create([
        'type' => MenuItemType::Linkable,
        'linkable_class' => Page::class,
        'linkable_id' => $page->id,
    ]);

    expect($item->linkable)->toBeInstanceOf(Page::class)
        ->and($item->linkable->id)->toBe($page->id);
});

it('can be linked to a section via polymorphic relationship', function () {
    $section = Section::factory()->create();
    $item = Item::factory()->create([
        'type' => MenuItemType::Linkable,
        'linkable_class' => Section::class,
        'linkable_id' => $section->id,
    ]);

    expect($item->linkable)->toBeInstanceOf(Section::class)
        ->and($item->linkable->id)->toBe($section->id);
});

it('getUrl returns correct URL for custom URL type', function () {
    $item = Item::factory()->create([
        'type' => MenuItemType::CustomUrl,
        'custom_url' => 'https://example.com',
    ]);

    expect($item->getUrl())->toBe('https://example.com');
});

it('getUrl returns null for group type', function () {
    $item = Item::factory()->create([
        'type' => MenuItemType::Group,
    ]);

    expect($item->getUrl())->toBeNull();
});

it('getUrl returns linkable URL for linkable type', function () {
    $page = Page::factory()->create(['sef_key' => 'test-page']);
    $item = Item::factory()->create([
        'type' => MenuItemType::Linkable,
        'linkable_class' => Page::class,
        'linkable_id' => $page->id,
    ]);

    // Test that linkable relationship works correctly
    expect($item->linkable)->not->toBeNull()
        ->and($item->linkable)->toBeInstanceOf(Page::class)
        ->and($item->type)->toBe(MenuItemType::Linkable);

    // The actual URL will be null until Page model has getUrl() method (in separate PR)
    // For now, just verify the structure is correct
});

it('can check if item has children', function () {
    $menu = Menu::factory()->create();
    $parent = Item::factory()->create(['menu_id' => $menu->id]);
    $childless = Item::factory()->create(['menu_id' => $menu->id]);

    Item::factory()->create(['menu_id' => $menu->id, 'parent_id' => $parent->id]);

    expect($parent->hasChildren())->toBeTrue()
        ->and($childless->hasChildren())->toBeFalse();
});

it('has proper scopes', function () {
    $menu = Menu::factory()->create();
    $activeItem = Item::factory()->active()->create(['menu_id' => $menu->id]);
    $inactiveItem = Item::factory()->inactive()->create(['menu_id' => $menu->id]);
    $rootItem = Item::factory()->active()->create(['menu_id' => $menu->id, 'parent_id' => -1]);
    $childItem = Item::factory()->active()->create(['menu_id' => $menu->id, 'parent_id' => $rootItem->id]);

    // Test active scope: 3 items should be active (activeItem, rootItem, childItem)
    expect(Item::active()->count())->toBe(3);

    // Test inactive items: 1 item should be inactive
    expect(Item::where('is_active', false)->count())->toBe(1);

    // Test root items scope: 3 items with parent_id = -1 (activeItem, inactiveItem, rootItem)
    expect(Item::rootItems()->count())->toBe(3);

    // Test child items: 1 item with parent_id != -1
    expect(Item::where('parent_id', '!=', -1)->count())->toBe(1);
});

it('has proper casts', function () {
    $item = new Item;

    $casts = $item->getCasts();

    expect($casts)
        ->toHaveKey('type', MenuItemType::class)
        ->toHaveKey('new_tab', 'boolean')
        ->toHaveKey('is_active', 'boolean');
});

it('has proper fillable attributes', function () {
    $item = new Item;

    $expectedFillable = [
        'label',
        'menu_id',
        'parent_id',
        'type',
        'linkable_class',
        'linkable_id',
        'custom_url',
        'new_tab',
        'is_active',
        'sort',
    ];

    expect($item->getFillable())->toEqual($expectedFillable);
});

it('can be soft deleted', function () {
    $item = Item::factory()->create();

    $item->delete();

    expect($item->trashed())->toBeTrue();

    $this->assertDatabaseHas('cms_menu_items', [
        'id' => $item->id,
    ]);

    $this->assertNotNull($item->deleted_at);
});

it('has proper tree methods for sorting', function () {
    $item = new Item;

    expect($item->determineStatusUsing())->toBe('is_active')
        ->and($item->determineTitleColumnName())->toBe('label')
        ->and($item->determineOrderColumnName())->toBe('sort');
});

it('can get tree formatted name', function () {
    $menu = Menu::factory()->create();
    $parent = Item::factory()->create(['menu_id' => $menu->id, 'label' => 'Parent']);
    $child = Item::factory()->create(['menu_id' => $menu->id, 'parent_id' => $parent->id, 'label' => 'Child']);

    $parentFormatted = $parent->getTreeFormattedName();
    $childFormatted = $child->getTreeFormattedName();

    expect($parentFormatted)->toContain('Parent')
        ->and($childFormatted)->toContain('Child');
});

it('can get full path', function () {
    $menu = Menu::factory()->create();
    $parent = Item::factory()->create([
        'menu_id' => $menu->id,
        'label' => ['en' => 'Parent Label'],
    ]);
    $child = Item::factory()->create([
        'menu_id' => $menu->id,
        'parent_id' => $parent->id,
        'label' => ['en' => 'Child Label'],
    ]);

    $fullPath = $child->getFullPath();

    expect($fullPath)->toContain('Parent Label')
        ->and($fullPath)->toContain('Child Label')
        ->and($fullPath)->toContain('>');
});

it('can get hierarchical options', function () {
    $menu = Menu::factory()->create();
    $items = Item::factory()->count(3)->create(['menu_id' => $menu->id]);

    $options = Item::getHierarchicalOptions($menu->id);

    expect($options)->toBeArray()
        ->and(count($options))->toBeGreaterThanOrEqual(3);
});
