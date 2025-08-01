<?php

use Eclipse\Cms\Enums\PageStatus;
use Eclipse\Cms\Models\Page;
use Eclipse\Cms\Models\Section;
use Filament\Facades\Filament;
use Illuminate\Validation\ValidationException;
use Workbench\App\Models\Site;

beforeEach(function () {
    $this->set_up_super_admin_and_tenant();
});

test('page can be created with valid data', function () {
    $site = Site::first();
    $section = Section::factory()->forSite($site)->create();

    $page = Page::create([
        'title' => ['en' => 'Test Page', 'sl' => 'Testna Stran'],
        'section_id' => $section->id,
        'short_text' => ['en' => 'Short description', 'sl' => 'Kratek opis'],
        'long_text' => ['en' => 'Long content', 'sl' => 'Dolga vsebina'],
        'sef_key' => ['en' => 'test-page', 'sl' => 'testna-stran'],
        'status' => PageStatus::Published,
    ]);

    expect($page)->toBeInstanceOf(Page::class);
    expect($page->title)->toBe('Test Page');
    expect($page->status)->toBe(PageStatus::Published);
    expect($page->section_id)->toBe($section->id);
});

test('page translatable fields work correctly', function () {
    $site = Site::first();
    $section = Section::factory()->forSite($site)->create();

    $page = Page::create([
        'title' => ['en' => 'English Title', 'sl' => 'Slovenski Naslov'],
        'section_id' => $section->id,
        'short_text' => ['en' => 'English short', 'sl' => 'Slovenski kratek'],
        'long_text' => ['en' => 'English long', 'sl' => 'Slovenski dolg'],
        'sef_key' => ['en' => 'english-title', 'sl' => 'slovenski-naslov'],
        'status' => PageStatus::Published,
    ]);

    expect($page->getTranslation('title', 'en'))->toBe('English Title');
    expect($page->getTranslation('title', 'sl'))->toBe('Slovenski Naslov');
    expect($page->getTranslation('sef_key', 'en'))->toBe('english-title');
    expect($page->getTranslation('sef_key', 'sl'))->toBe('slovenski-naslov');
});

test('page auto-generates sef_key from title when empty', function () {
    $site = Site::first();
    $section = Section::factory()->forSite($site)->create();

    $page = Page::create([
        'title' => ['en' => 'Auto Generated SEF Key'],
        'section_id' => $section->id,
        'status' => PageStatus::Draft,
    ]);

    expect($page->getTranslation('sef_key', 'en'))->toBe('auto-generated-sef-key');
});

test('page copies section type to page type', function () {
    $site = Site::first();
    $section = Section::factory()->forSite($site)->create([
        'type' => \Eclipse\Cms\Enums\SectionType::Pages,
    ]);

    $page = Page::create([
        'title' => ['en' => 'Test Page'],
        'section_id' => $section->id,
        'status' => PageStatus::Draft,
    ]);

    expect($page->type)->toBe('Pages');
});

test('page validates unique sef_key per site', function () {
    $site = Site::first();
    $section = Section::factory()->forSite($site)->create();

    // Create first page with simple string sef_key (will be auto-converted to translatable)
    Page::create([
        'title' => 'First Page',
        'section_id' => $section->id,
        'sef_key' => 'unique-key',
        'status' => PageStatus::Published,
    ]);

    // Try to create second page with same sef_key - should throw validation exception
    expect(function () use ($section) {
        Page::create([
            'title' => 'Second Page',
            'section_id' => $section->id,
            'sef_key' => 'unique-key',
            'status' => PageStatus::Published,
        ]);
    })->toThrow(ValidationException::class);
});

test('pages from different sites can have same sef_key', function () {
    $site1 = Site::first();
    $site2 = Site::factory()->create();

    // Clear tenant so sections can be created with explicit site_id
    Filament::setTenant(null);

    $section1 = Section::factory()->forSite($site1)->create();
    $section2 = Section::factory()->forSite($site2)->create();

    // Create page on site1
    $page1 = Page::create([
        'title' => ['en' => 'Same SEF Key Page'],
        'section_id' => $section1->id,
        'sef_key' => ['en' => 'same-key'],
        'status' => PageStatus::Published,
    ]);

    // Create page on site2 with same sef_key - should work
    $page2 = Page::create([
        'title' => ['en' => 'Same SEF Key Page'],
        'section_id' => $section2->id,
        'sef_key' => ['en' => 'same-key'],
        'status' => PageStatus::Published,
    ]);

    expect($page1->getTranslation('sef_key', 'en'))->toBe('same-key');
    expect($page2->getTranslation('sef_key', 'en'))->toBe('same-key');
});

test('page is scoped to current tenant sections', function () {
    $site1 = Site::first();
    $site2 = Site::factory()->create();

    // Clear tenant so sections can be created with explicit site_id
    Filament::setTenant(null);

    $section1 = Section::factory()->forSite($site1)->create();
    $section2 = Section::factory()->forSite($site2)->create();

    $page1 = Page::factory()->forSection($section1)->create();
    $page2 = Page::factory()->forSection($section2)->create();

    // When tenant is site1, only pages from site1 sections should be visible
    Filament::setTenant($site1);
    expect(Page::count())->toBe(1);
    expect(Page::first()->id)->toBe($page1->id);

    // When tenant is site2, only pages from site2 sections should be visible
    Filament::setTenant($site2);
    expect(Page::count())->toBe(1);
    expect(Page::first()->id)->toBe($page2->id);
});

test('page belongs to section', function () {
    $site = Site::first();
    $section = Section::factory()->forSite($site)->create();

    $page = Page::factory()->forSection($section)->create();

    expect($page->section)->toBeInstanceOf(Section::class);
    expect($page->section->id)->toBe($section->id);
});

test('page can be updated', function () {
    $site = Site::first();
    $section = Section::factory()->forSite($site)->create();
    $page = Page::factory()->forSection($section)->create();

    $originalId = $page->id;
    $originalTitle = $page->title;

    // Update the page
    $page->update([
        'title' => ['en' => 'Updated Title'],
        'short_text' => ['en' => 'Updated short text'],
        'status' => PageStatus::Published,
    ]);

    // Refresh to get latest data from database
    $page->refresh();

    expect($page->id)->toBe($originalId);
    expect($page->title)->toBe('Updated Title');
    expect($page->title)->not->toBe($originalTitle);
    expect($page->getTranslation('short_text', 'en'))->toBe('Updated short text');
    expect($page->status)->toBe(PageStatus::Published);
});

test('page can be soft deleted', function () {
    $site = Site::first();
    $section = Section::factory()->forSite($site)->create();
    $page = Page::factory()->forSection($section)->create();

    $pageId = $page->id;

    // Soft delete the page
    $page->delete();

    // Page should not be found in normal queries
    expect(Page::find($pageId))->toBeNull();
    expect(Page::count())->toBe(0);

    // But should be found with trashed
    expect(Page::withTrashed()->find($pageId))->not->toBeNull();
    expect(Page::withTrashed()->count())->toBe(1);
    expect(Page::onlyTrashed()->count())->toBe(1);
});

test('page can be restored after soft delete', function () {
    $site = Site::first();
    $section = Section::factory()->forSite($site)->create();
    $page = Page::factory()->forSection($section)->create();

    $pageId = $page->id;

    // Soft delete and restore
    $page->delete();
    expect(Page::find($pageId))->toBeNull();

    $page->restore();

    // Page should be accessible again
    expect(Page::find($pageId))->not->toBeNull();
    expect(Page::count())->toBe(1);
    expect(Page::onlyTrashed()->count())->toBe(0);
});

test('page can be force deleted', function () {
    $site = Site::first();
    $section = Section::factory()->forSite($site)->create();
    $page = Page::factory()->forSection($section)->create();

    $pageId = $page->id;

    // Force delete the page
    $page->forceDelete();

    // Page should not exist anywhere
    expect(Page::find($pageId))->toBeNull();
    expect(Page::withTrashed()->find($pageId))->toBeNull();
    expect(Page::withTrashed()->count())->toBe(0);
});

test('page search functionality works correctly', function () {
    $site = Site::first();
    $section = Section::factory()->forSite($site)->create();

    $page = Page::create([
        'title' => ['en' => 'Searchable Page Title'],
        'section_id' => $section->id,
        'short_text' => ['en' => 'This is searchable content'],
        'long_text' => ['en' => 'More detailed searchable content here'],
        'sef_key' => ['en' => 'searchable-page'],
        'status' => PageStatus::Published,
    ]);

    $searchArray = $page->toSearchableArray();

    expect($searchArray)->toHaveKey('title');
    expect($searchArray)->toHaveKey('short_text');
    expect($searchArray)->toHaveKey('long_text');
    expect($searchArray)->toHaveKey('sef_key');
    expect($searchArray)->toHaveKey('status');
    expect($searchArray)->toHaveKey('section_id');

    // Check translatable fields are properly formatted
    expect($searchArray['title'])->toBeArray();
    expect($searchArray['title']['en'])->toBe('Searchable Page Title');
});

test('page validation prevents creation with invalid data', function () {
    $site = Site::first();
    $section = Section::factory()->forSite($site)->create();

    // Test missing required title - should throw database error
    expect(function () use ($section) {
        Page::create([
            'section_id' => $section->id,
            'status' => PageStatus::Draft,
        ]);
    })->toThrow(\Exception::class);

    // Test missing required section_id - should throw database error
    expect(function () {
        Page::create([
            'title' => ['en' => 'Title'],
            'status' => PageStatus::Draft,
        ]);
    })->toThrow(\Exception::class);
});
