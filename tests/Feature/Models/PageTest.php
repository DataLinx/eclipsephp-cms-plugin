<?php

use Eclipse\Cms\Enums\PageStatus;
use Eclipse\Cms\Models\Page;
use Eclipse\Cms\Models\Section;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->set_up_super_admin_and_tenant();
});

test('page can be created with valid data', function () {
    $section = Section::factory()->create();

    $page = Page::create([
        'title' => ['en' => 'Test Page', 'sl' => 'Testna Stran'],
        'short_text' => ['en' => 'Short description', 'sl' => 'Kratek opis'],
        'long_text' => ['en' => 'Long content', 'sl' => 'Dolga vsebina'],
        'sef_key' => ['en' => 'test-page', 'sl' => 'testna-stran'],
        'status' => PageStatus::Published,
        'type' => 'page',
        'section_id' => $section->id,
    ]);

    expect($page)->toBeInstanceOf(Page::class);
    expect($page->title)->toBe('Test Page');
    expect($page->status)->toBe(PageStatus::Published);
});

test('page translatable fields work correctly', function () {
    $section = Section::factory()->create();

    $page = Page::create([
        'title' => ['en' => 'English Title', 'sl' => 'Slovenski Naslov'],
        'short_text' => ['en' => 'English short', 'sl' => 'Slovenski kratek'],
        'long_text' => ['en' => 'English long', 'sl' => 'Slovenski dolg'],
        'sef_key' => ['en' => 'english-title', 'sl' => 'slovenski-naslov'],
        'status' => PageStatus::Published,
        'type' => 'page',
        'section_id' => $section->id,
    ]);

    expect($page->getTranslation('title', 'en'))->toBe('English Title');
    expect($page->getTranslation('title', 'sl'))->toBe('Slovenski Naslov');
    expect($page->getTranslation('sef_key', 'en'))->toBe('english-title');
    expect($page->getTranslation('sef_key', 'sl'))->toBe('slovenski-naslov');
});

test('page auto-generates sef_key from title when empty', function () {
    $section = Section::factory()->create();

    $page = Page::create([
        'title' => ['en' => 'Auto Generated SEF Key'],
        'short_text' => ['en' => 'Short description'],
        'long_text' => ['en' => 'Long content'],
        'status' => PageStatus::Published,
        'type' => 'page',
        'section_id' => $section->id,
    ]);

    expect($page->sef_key)->toBe('auto-generated-sef-key');
});

test('page validates unique sef_key', function () {
    $section = Section::factory()->create();

    Page::create([
        'title' => ['en' => 'First Page'],
        'sef_key' => ['en' => 'unique-key'],
        'status' => PageStatus::Published,
        'type' => 'page',
        'section_id' => $section->id,
    ]);

    expect(function () use ($section) {
        Page::create([
            'title' => ['en' => 'Second Page'],
            'sef_key' => ['en' => 'unique-key'],
            'status' => PageStatus::Published,
            'type' => 'page',
            'section_id' => $section->id,
        ]);
    })->toThrow(ValidationException::class);
});

test('page can be updated', function () {
    $page = Page::factory()->create();

    $page->update([
        'title' => ['en' => 'Updated Title'],
        'status' => PageStatus::Draft,
    ]);

    expect($page->fresh()->title)->toBe('Updated Title');
    expect($page->fresh()->status)->toBe(PageStatus::Draft);
});

test('page can be soft deleted', function () {
    $page = Page::factory()->create();

    $page->delete();

    expect($page->trashed())->toBeTrue();
    expect(Page::count())->toBe(0);
    expect(Page::withTrashed()->count())->toBe(1);
});

test('page can be restored after soft delete', function () {
    $page = Page::factory()->create();
    $page->delete();

    $page->restore();

    expect($page->trashed())->toBeFalse();
    expect(Page::count())->toBe(1);
});

test('page can be force deleted', function () {
    $page = Page::factory()->create();

    $page->forceDelete();

    expect(Page::withTrashed()->count())->toBe(0);
});

test('page search functionality works correctly', function () {
    $section = Section::factory()->create();

    $page = Page::factory()->forSection($section)->create([
        'title' => ['en' => 'Searchable Title'],
        'short_text' => ['en' => 'Searchable content'],
    ]);

    $searchData = $page->toSearchableArray();

    expect($searchData)->toHaveKeys([
        'id', 'title', 'short_text', 'long_text',
        'sef_key', 'status', 'type',
    ]);
    expect($searchData['title'])->toBe(['en' => 'Searchable Title']);
});

test('page validation prevents creation with invalid data', function () {
    expect(function () {
        Page::create([
            'title' => '',
            'status' => 'invalid-status',
        ]);
    })->toThrow(ValueError::class);
});
