<?php

use Eclipse\Cms\Enums\SectionType;
use Eclipse\Cms\Models\Section;
use Filament\Facades\Filament;
use Workbench\App\Models\Site;

beforeEach(function () {
    $this->set_up_super_admin_and_tenant();
});

test('section can be created with valid data', function () {
    $site = Site::first();

    $section = Section::create([
        'name' => ['en' => 'Test Section', 'sl' => 'Testna Sekcija'],
        'type' => SectionType::Pages,
        'site_id' => $site->id,
    ]);

    expect($section)->toBeInstanceOf(Section::class);
    expect($section->name)->toBe('Test Section');
    expect($section->type)->toBe(SectionType::Pages);
    expect($section->site_id)->toBe($site->id);
});

test('section name is translatable', function () {
    $site = Site::first();

    $section = Section::create([
        'name' => ['en' => 'Information', 'sl' => 'Informacije'],
        'type' => SectionType::Pages,
        'site_id' => $site->id,
    ]);

    expect($section->getTranslation('name', 'en'))->toBe('Information');
    expect($section->getTranslation('name', 'sl'))->toBe('Informacije');
});

test('section is automatically scoped to current tenant', function () {
    $site1 = Site::first();
    $site2 = Site::factory()->create();

    // Clear tenant so sections can be created with explicit site_id
    Filament::setTenant(null);

    // Create section for site1
    $section1 = Section::create([
        'name' => ['en' => 'Site 1 Section'],
        'type' => SectionType::Pages,
        'site_id' => $site1->id,
    ]);

    // Create section for site2
    $section2 = Section::create([
        'name' => ['en' => 'Site 2 Section'],
        'type' => SectionType::Pages,
        'site_id' => $site2->id,
    ]);

    // When tenant is site1, only site1 sections should be visible
    Filament::setTenant($site1);
    expect(Section::count())->toBe(1);
    expect(Section::first()->id)->toBe($section1->id);

    // When tenant is site2, only site2 sections should be visible
    Filament::setTenant($site2);
    expect(Section::count())->toBe(1);
    expect(Section::first()->id)->toBe($section2->id);
});

test('section automatically gets site_id from current tenant when created', function () {
    $site = Site::first();
    Filament::setTenant($site);

    $section = Section::create([
        'name' => ['en' => 'Auto Site Section'],
        'type' => SectionType::Pages,
    ]);

    expect($section->site_id)->toBe($site->id);
});

test('section has pages relationship', function () {
    $site = Site::first();

    $section = Section::factory()->forSite($site)->create();

    expect($section->pages())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

test('section belongs to site', function () {
    $site = Site::first();

    $section = Section::factory()->forSite($site)->create();

    expect($section->site)->toBeInstanceOf(Site::class);
    expect($section->site->id)->toBe($site->id);
});
