<?php

use Eclipse\Cms\Models\Menu;
use Eclipse\Cms\Models\Section;

it('menu model has proper tenancy fillable attributes', function () {
    config(['eclipse-cms.tenancy.enabled' => true]);
    config(['eclipse-cms.tenancy.foreign_key' => 'site_id']);

    $menu = new Menu;
    $fillable = $menu->getFillable();

    expect($fillable)->toContain('site_id');

    config(['eclipse-cms.tenancy.enabled' => false]);
});

it('section model has proper tenancy fillable attributes', function () {
    config(['eclipse-cms.tenancy.enabled' => true]);
    config(['eclipse-cms.tenancy.foreign_key' => 'site_id']);

    $section = new Section;
    $fillable = $section->getFillable();

    expect($fillable)->toContain('site_id');

    config(['eclipse-cms.tenancy.enabled' => false]);
});

it('tenancy configuration is properly handled', function () {
    expect(config('eclipse-cms.tenancy.enabled'))->toBeFalse();
    expect(config('eclipse-cms.tenancy.model'))->toBe('Workbench\\App\\Models\\Site');
    expect(config('eclipse-cms.tenancy.foreign_key'))->toBe('site_id');
});
