<?php

use Eclipse\Cms\Models\Menu;
use Eclipse\Cms\Models\Menu\Item;

it('can create a menu', function () {
    $menu = Menu::factory()->create([
        'title' => ['en' => 'Test Menu', 'sl' => 'Test Meni'],
        'code' => 'test-menu',
        'is_active' => true,
    ]);

    expect($menu)
        ->getTranslations('title')->toBe(['en' => 'Test Menu', 'sl' => 'Test Meni'])
        ->code->toBe('test-menu')
        ->is_active->toBe(true);
});

it('has translatable title', function () {
    $menu = Menu::factory()->create([
        'title' => ['en' => 'English Title', 'sl' => 'Slovenian Title'],
    ]);

    app()->setLocale('en');
    expect($menu->title)->toBe('English Title');

    app()->setLocale('sl');
    expect($menu->title)->toBe('Slovenian Title');
});

it('can have menu items', function () {
    $menu = Menu::factory()->create();
    $items = Item::factory()->count(3)->create(['menu_id' => $menu->id]);

    expect($menu->allItems)->toHaveCount(3);
    $menuItemIds = $menu->allItems->pluck('id')->toArray();
    $factoryItemIds = $items->pluck('id')->toArray();

    foreach ($factoryItemIds as $id) {
        expect($menuItemIds)->toContain($id);
    }
});

it('items relationship returns only root items', function () {
    $menu = Menu::factory()->create();

    $rootItem = Item::factory()->create(['menu_id' => $menu->id, 'parent_id' => -1]);
    $childItem = Item::factory()->create(['menu_id' => $menu->id, 'parent_id' => $rootItem->id]);

    expect($menu->items)->toHaveCount(1)
        ->and($menu->items->first()->id)->toBe($rootItem->id)
        ->and($menu->allItems)->toHaveCount(2);
});

it('can be soft deleted', function () {
    $menu = Menu::factory()->create();

    $menu->delete();

    expect($menu->trashed())->toBeTrue();

    $this->assertDatabaseHas('cms_menus', [
        'id' => $menu->id,
    ]);

    $this->assertNotNull($menu->deleted_at);
});

it('has proper fillable attributes', function () {
    $menu = new Menu;

    $expectedFillable = [
        'title',
        'is_active',
        'code',
    ];

    expect($menu->getFillable())->toEqual($expectedFillable);
});

it('has proper fillable attributes with tenancy enabled', function () {
    config(['eclipse-cms.tenancy.enabled' => true]);
    config(['eclipse-cms.tenancy.foreign_key' => 'site_id']);

    $menu = new Menu;

    $expectedFillable = [
        'title',
        'is_active',
        'code',
        'site_id',
    ];

    expect($menu->getFillable())->toEqual($expectedFillable);

    config(['eclipse-cms.tenancy.enabled' => false]);
});

it('has proper casts', function () {
    $menu = new Menu;

    expect($menu->getCasts())->toHaveKey('is_active', 'boolean');
});

it('has translatable fields', function () {
    $menu = new Menu;

    expect($menu->translatable)->toContain('title');
});
