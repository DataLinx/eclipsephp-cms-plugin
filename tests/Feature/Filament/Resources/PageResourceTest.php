<?php

use Eclipse\Cms\Admin\Filament\Resources\PageResource;
use Eclipse\Cms\CmsPlugin;
use Eclipse\Cms\Enums\PageStatus;
use Eclipse\Cms\Models\Page;
use Eclipse\Cms\Models\Section;
use Livewire\Livewire;

beforeEach(function () {
    $this->setUpSuperAdmin();
});

test('authorized access can view pages list', function () {
    Page::factory()->count(3)->create();

    Livewire::test(PageResource\Pages\ListPages::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords(Page::all());
});

test('create page screen can be rendered', function () {
    Livewire::test(PageResource\Pages\CreatePage::class)
        ->assertSuccessful();
});

test('page form validation works', function () {
    Livewire::test(PageResource\Pages\CreatePage::class)
        ->fillForm([
            'title' => '',
        ])
        ->call('create')
        ->assertHasFormErrors(['title' => 'required']);
});

test('page can be created through form', function () {
    $section = Section::factory()->create();
    $initialCount = Page::count();

    Livewire::test(PageResource\Pages\CreatePage::class)
        ->fillForm([
            'title' => ['en' => 'Test Page'],
            'short_text' => ['en' => 'Short description'],
            'long_text' => ['en' => 'Long content'],
            'status' => PageStatus::Published,
            'type' => 'page',
            'section_id' => $section->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Page::count())->toBe($initialCount + 1);

    $page = Page::latest()->first();
    $title = $page->getTranslation('title', 'en');

    $expectedTitle = is_array($title) ? ($title['en'] ?? $title) : $title;
    expect($expectedTitle)->toBe('Test Page');
});

test('sef_key is auto-generated from title when empty', function () {
    $section = Section::factory()->create();

    Livewire::test(PageResource\Pages\CreatePage::class)
        ->fillForm([
            'title' => ['en' => 'Auto SEF Key'],
            'status' => PageStatus::Published,
            'type' => 'page',
            'section_id' => $section->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $page = Page::latest()->first();
    $sefKey = $page->sef_key;
    $expectedSefKey = is_array($sefKey) ? ($sefKey['en'] ?? $sefKey) : $sefKey;
    expect($expectedSefKey)->toBe('auto-sef-key');
});

test('pages can be filtered by status', function () {
    Page::factory()->create(['status' => PageStatus::Published]);
    Page::factory()->create(['status' => PageStatus::Draft]);

    Livewire::test(PageResource\Pages\ListPages::class)
        ->filterTable('status', PageStatus::Published->value)
        ->assertCanSeeTableRecords(Page::where('status', PageStatus::Published)->get())
        ->assertCanNotSeeTableRecords(Page::where('status', PageStatus::Draft)->get());
});

test('page can be updated', function () {
    $page = Page::factory()->create();

    Livewire::test(PageResource\Pages\EditPage::class, [
        'record' => $page->getRouteKey(),
    ])
        ->fillForm([
            'title' => ['en' => 'Updated Title'],
            'status' => PageStatus::Draft,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $updatedPage = $page->fresh();
    $titleValue = $updatedPage->getTranslation('title', 'en');
    $expectedTitle = is_array($titleValue) ? ($titleValue['en'] ?? $titleValue) : $titleValue;
    expect($expectedTitle)->toBe('Updated Title');
    expect($page->fresh()->status)->toBe(PageStatus::Draft);
});

test('page can be deleted', function () {
    $page = Page::factory()->create();

    Livewire::test(PageResource\Pages\EditPage::class, [
        'record' => $page->getRouteKey(),
    ])
        ->callAction('delete');

    expect($page->fresh()->trashed())->toBeTrue();
});

test('unauthorized access can be prevented', function () {
    $this->setUpUserWithoutPermissions();

    Livewire::test(PageResource\Pages\ListPages::class)
        ->assertForbidden();
});

test('user with create permission can create pages', function () {
    $this->setUpUserWithPermissions(['view_any_page', 'create_page']);

    Livewire::test(PageResource\Pages\CreatePage::class)
        ->assertSuccessful();
});

test('user with update permission can edit pages', function () {
    $this->setUpUserWithPermissions(['view_any_page', 'view_page', 'update_page']);
    $page = Page::factory()->create();

    Livewire::test(PageResource\Pages\EditPage::class, [
        'record' => $page->getRouteKey(),
    ])
        ->assertSuccessful();
});

test('user with delete permission can delete pages', function () {
    $this->setUpUserWithPermissions(['view_any_page', 'view_page', 'delete_page']);
    $page = Page::factory()->create();

    $pageExists = Page::where('id', $page->id)->exists();
    expect($pageExists)->toBeTrue();

    $page->delete();

    expect($page->fresh()->trashed())->toBeTrue();
});

test('pages can be filtered by section via URL parameter', function () {
    $section1 = Section::factory()->create(['name' => ['en' => 'Section 1']]);
    $section2 = Section::factory()->create(['name' => ['en' => 'Section 2']]);

    $page1 = Page::factory()->forSection($section1)->create();
    $page2 = Page::factory()->forSection($section2)->create();

    $response = $this->get(PageResource::getUrl('index').'?section='.$section1->id);

    $response->assertSuccessful();
    $response->assertSee($page1->title);
    $response->assertDontSee($page2->title);
});

test('section navigation items generate correct URLs', function () {
    $section = Section::factory()->create(['name' => ['en' => 'Test Section']]);

    $plugin = new CmsPlugin;
    $navigationItems = $plugin->getSectionNavigationItems();

    expect($navigationItems)->toHaveCount(1);

    $item = $navigationItems[0];
    expect($item->getLabel())->toBe('Test Section');
    expect($item->getUrl())->toContain('section='.$section->id);
    expect($item->getGroup())->toBe('CMS');
});
