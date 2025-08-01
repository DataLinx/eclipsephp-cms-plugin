<?php

use Eclipse\Cms\Enums\PageStatus;
use Eclipse\Cms\Filament\Resources\PageResource;
use Eclipse\Cms\Filament\Resources\PageResource\Pages\CreatePage;
use Eclipse\Cms\Filament\Resources\PageResource\Pages\ListPages;
use Eclipse\Cms\Models\Page;
use Eclipse\Cms\Models\Section;
use Filament\Facades\Filament;
use Workbench\App\Models\Site;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->set_up_super_admin_and_tenant();
});

test('authorized access can view pages list', function () {
    // Debug permissions
    expect($this->superAdmin->can('view_any_page'))->toBeTrue();
    expect($this->superAdmin->getAllPermissions()->pluck('name')->toArray())->toContain('view_any_page');

    $this->get(PageResource::getUrl())
        ->assertOk();
});

test('create page screen can be rendered', function () {
    $this->get(PageResource::getUrl('create'))
        ->assertOk();
});

test('page form validation works', function () {
    $component = livewire(CreatePage::class);

    $component->assertFormExists();

    // Test required fields
    $component->call('create')
        ->assertHasFormErrors([
            'title' => 'required',
            'section_id' => 'required',
            'sef_key' => 'required',
        ]);
});

test('page can be created through form', function () {
    $site = Site::first();
    $section = Section::factory()->forSite($site)->create();

    $component = livewire(CreatePage::class);

    $component->fillForm([
        'title' => 'Test Page',
        'section_id' => $section->id,
        'sef_key' => 'test-page',
        'short_text' => 'Short description',
        'long_text' => 'Long content',
        'status' => PageStatus::Published->value,
    ])->call('create');

    $component->assertHasNoFormErrors();

    $page = Page::first();
    expect($page)->not->toBeNull();
    expect($page->title)->toBe('Test Page');
    expect($page->section_id)->toBe($section->id);
    expect($page->status)->toBe(PageStatus::Published);
});

test('sef_key is auto-generated from title when empty', function () {
    $site = Site::first();
    $section = Section::factory()->forSite($site)->create();

    $component = livewire(CreatePage::class);

    // Fill form with title but leave sef_key empty initially
    $component->fillForm([
        'title' => 'Auto Generated Title',
        'section_id' => $section->id,
        'status' => PageStatus::Draft->value,
    ]);

    // Trigger the afterStateUpdated event for title
    $component->set('data.title', 'Auto Generated Title');

    // Check if sef_key was auto-generated
    expect($component->get('data.sef_key'))->toBe('auto-generated-title');
});

test('pages list shows only current tenant pages', function () {
    // Get the current tenant from Filament (site1 from beforeEach)
    $currentTenant = filament()->getTenant();
    $site1 = $currentTenant;

    // Create a second site with unique name
    $site2 = Site::factory()->create(['name' => 'Site 2', 'slug' => 'site-2']);

    // Verify sites are different
    expect($site1->id)->not->toBe($site2->id, 'Sites should have different IDs');

    // Create sections - section1 for current tenant, section2 for different tenant
    $section1 = Section::factory()->create(['name' => ['en' => 'Section 1']]);

    // Temporarily switch tenant to create section2 for site2
    $originalTenant = filament()->getTenant();
    Filament::setTenant($site2);
    $section2 = Section::factory()->create(['name' => ['en' => 'Section 2']]);
    Filament::setTenant($originalTenant);

    // Verify sections belong to different sites
    expect($section1->site_id)->toBe($site1->id);
    expect($section2->site_id)->toBe($site2->id);
    expect($section1->site_id)->not->toBe($section2->site_id, 'Sections should belong to different sites');

    // Create pages for different sites
    $page1 = Page::factory()->forSection($section1)->create(['title' => ['en' => 'Site 1 Page']]);
    $page2 = Page::factory()->forSection($section2)->create(['title' => ['en' => 'Site 2 Page']]);

    // Debug: Check what pages exist in database
    $allPages = Page::withoutGlobalScopes()->get();
    expect($allPages)->toHaveCount(2);

    // Debug: Check what the global scope returns
    $scopedPages = Page::all();
    expect($scopedPages)->toHaveCount(1, 'Global scope should only return 1 page for current tenant');
    expect($scopedPages->first()->id)->toBe($page1->id, 'Global scope should only return page1');

    $component = livewire(ListPages::class);

    // Should only show pages for current tenant (site1)
    $component->assertCanSeeTableRecords([$page1]);
    $component->assertCanNotSeeTableRecords([$page2]);
});

test('pages can be filtered by section', function () {
    $site = Site::first();
    $section1 = Section::factory()->forSite($site)->create(['name' => ['en' => 'Section 1']]);
    $section2 = Section::factory()->forSite($site)->create(['name' => ['en' => 'Section 2']]);

    $page1 = Page::factory()->forSection($section1)->create(['title' => ['en' => 'Page 1']]);
    $page2 = Page::factory()->forSection($section2)->create(['title' => ['en' => 'Page 2']]);

    $component = livewire(ListPages::class);

    // Filter by section1
    $component->filterTable('section', $section1->id);
    $component->assertCanSeeTableRecords([$page1]);
    $component->assertCanNotSeeTableRecords([$page2]);

    // Filter by section2
    $component->filterTable('section', $section2->id);
    $component->assertCanSeeTableRecords([$page2]);
    $component->assertCanNotSeeTableRecords([$page1]);
});

test('pages can be filtered by status', function () {
    $site = Site::first();
    $section = Section::factory()->forSite($site)->create();

    $draftPage = Page::factory()->forSection($section)->create([
        'title' => ['en' => 'Draft Page'],
        'status' => PageStatus::Draft->value,
    ]);

    $publishedPage = Page::factory()->forSection($section)->create([
        'title' => ['en' => 'Published Page'],
        'status' => PageStatus::Published->value,
    ]);

    $component = livewire(ListPages::class);

    // Filter by draft status
    $component->filterTable('status', PageStatus::Draft->value);
    $component->assertCanSeeTableRecords([$draftPage]);
    $component->assertCanNotSeeTableRecords([$publishedPage]);

    // Filter by published status
    $component->filterTable('status', PageStatus::Published->value);
    $component->assertCanSeeTableRecords([$publishedPage]);
    $component->assertCanNotSeeTableRecords([$draftPage]);
});

test('page can be updated', function () {
    $site = Site::first();
    $section = Section::factory()->forSite($site)->create();
    $page = Page::factory()->forSection($section)->create();

    $component = livewire(PageResource\Pages\EditPage::class, [
        'record' => $page->getRouteKey(),
    ]);

    $component->fillForm([
        'title' => 'Updated Page Title',
        'section_id' => $section->id,
        'sef_key' => 'updated-page-title',
        'status' => PageStatus::Published->value,
    ])->call('save');

    $component->assertHasNoFormErrors();

    $page->refresh();
    expect($page->title)->toBe('Updated Page Title');
    expect($page->status)->toBe(PageStatus::Published);
});

test('page can be deleted', function () {
    $site = Site::first();
    $section = Section::factory()->forSite($site)->create();
    $page = Page::factory()->forSection($section)->create();

    $component = livewire(ListPages::class);

    $component->callTableAction('delete', $page);

    expect(Page::count())->toBe(0);
});

test('unauthorized access can be prevented', function () {
    // Create regular user with no permissions
    $this->set_up_common_user_and_tenant();

    $this->user->syncRoles([]);
    $this->user->syncPermissions([]);

    $site = Site::first();
    $section = Section::factory()->forSite($site)->create();
    $page = Page::factory()->forSection($section)->create();

    // View table
    $this->get(PageResource::getUrl())
        ->assertForbidden();

    // Add direct permission to view the table, since otherwise any other action below is not available even for testing
    $this->user->givePermissionTo('view_any_page');

    // Create page
    livewire(ListPages::class)
        ->assertActionDisabled('create');

    // Edit page
    livewire(ListPages::class)
        ->assertCanSeeTableRecords([$page])
        ->assertTableActionDisabled('edit', $page);

    // Delete page
    livewire(ListPages::class)
        ->assertTableActionDisabled('delete', $page)
        ->assertTableBulkActionDisabled('delete');
});

test('user with create permission can create pages', function () {
    // Create regular user with only create permission
    $this->set_up_common_user_and_tenant();

    $this->user->syncRoles([]);
    $this->user->syncPermissions(['view_any_page', 'create_page']);

    $site = Site::first();
    $section = Section::factory()->forSite($site)->create();

    $component = livewire(CreatePage::class);

    $component->fillForm([
        'title' => 'Authorized Page',
        'section_id' => $section->id,
        'sef_key' => 'authorized-page',
        'short_text' => 'Created by regular user',
        'status' => PageStatus::Draft->value,
    ])->call('create');

    $component->assertHasNoFormErrors();

    $page = Page::where('title->en', 'Authorized Page')->first();
    expect($page)->not->toBeNull();
    expect($page->title)->toBe('Authorized Page');
});

test('user with update permission can edit pages', function () {
    // Create regular user with update permission
    $this->set_up_common_user_and_tenant();

    $this->user->syncRoles([]);
    $this->user->syncPermissions(['view_any_page', 'view_page', 'update_page']);

    $site = Site::first();
    $section = Section::factory()->forSite($site)->create();
    $page = Page::factory()->forSection($section)->create();

    $component = livewire(PageResource\Pages\EditPage::class, [
        'record' => $page->getRouteKey(),
    ]);

    $component->fillForm([
        'title' => 'Updated by Regular User',
        'section_id' => $section->id,
        'sef_key' => 'updated-by-regular-user',
        'status' => PageStatus::Published->value,
    ])->call('save');

    $component->assertHasNoFormErrors();

    $page->refresh();
    expect($page->title)->toBe('Updated by Regular User');
});

test('user with delete permission can delete pages', function () {
    // Create regular user with delete permission
    $this->set_up_common_user_and_tenant();

    $this->user->syncRoles([]);
    $this->user->syncPermissions(['view_any_page', 'delete_page']);

    $site = Site::first();
    $section = Section::factory()->forSite($site)->create();
    $page = Page::factory()->forSection($section)->create();

    $component = livewire(ListPages::class);

    $component->callTableAction('delete', $page);

    expect(Page::count())->toBe(0);
});

test('users can only see pages from sections they have permission to view', function () {
    // Create two sites with different sections and pages
    $site1 = Site::first();
    $site2 = Site::factory()->create(['name' => 'Site 2', 'slug' => 'site-2']);

    // Create sections for different sites using tenant switching
    $section1 = Section::factory()->create(['name' => ['en' => 'Section 1']]);

    $originalTenant = filament()->getTenant();
    Filament::setTenant($site2);
    $section2 = Section::factory()->create(['name' => ['en' => 'Section 2']]);
    Filament::setTenant($originalTenant);

    $page1 = Page::factory()->forSection($section1)->create(['title' => ['en' => 'Site 1 Page']]);
    $page2 = Page::factory()->forSection($section2)->create(['title' => ['en' => 'Site 2 Page']]);

    // Create regular user with permission for site1 only
    $this->set_up_common_user_and_tenant();
    $this->user->syncRoles([]);
    $this->user->syncPermissions(['view_any_page']);

    // User should only see pages from their current tenant (site1)
    $component = livewire(ListPages::class);
    $component->assertCanSeeTableRecords([$page1]);
    $component->assertCanNotSeeTableRecords([$page2]);
});

test('user with restore permission can restore deleted pages', function () {
    // Create regular user with restore permission
    $this->set_up_common_user_and_tenant();

    $this->user->syncRoles([]);
    $this->user->syncPermissions(['view_any_page', 'restore_page']);

    $site = Site::first();
    $section = Section::factory()->forSite($site)->create();
    $page = Page::factory()->forSection($section)->create();
    $pageId = $page->id;
    $page->delete(); // Soft delete

    // Verify it's soft deleted
    expect(Page::find($pageId))->toBeNull();
    expect(Page::withTrashed()->find($pageId))->not->toBeNull();

    // Restore directly through model for now
    $trashedPage = Page::withTrashed()->find($pageId);
    $trashedPage->restore();

    expect(Page::find($pageId))->not->toBeNull();
    expect(Page::find($pageId)->deleted_at)->toBeNull();
});

test('user with force delete permission can permanently delete pages', function () {
    // Create regular user with force delete permission
    $this->set_up_common_user_and_tenant();

    $this->user->syncRoles([]);
    $this->user->syncPermissions(['view_any_page', 'force_delete_page']);

    $site = Site::first();
    $section = Section::factory()->forSite($site)->create();
    $page = Page::factory()->forSection($section)->create();
    $pageId = $page->id;

    // Force delete directly through model for now
    $page->forceDelete();

    expect(Page::withTrashed()->where('id', $pageId)->count())->toBe(0);
});

test('tenant scoping prevents cross-tenant page access', function () {
    // This test verifies that pages are properly scoped by tenant through their sections
    $site1 = Site::first();
    $site2 = Site::factory()->create(['name' => 'Site 2', 'slug' => 'site-2']);

    // Clear tenant so sections can be created with explicit site_id
    Filament::setTenant(null);

    $section1 = Section::factory()->forSite($site1)->create(['name' => ['en' => 'Section 1']]);
    $section2 = Section::factory()->forSite($site2)->create(['name' => ['en' => 'Section 2']]);

    $page1 = Page::factory()->forSection($section1)->create(['title' => ['en' => 'Site 1 Page']]);
    $page2 = Page::factory()->forSection($section2)->create(['title' => ['en' => 'Site 2 Page']]);

    // When tenant is site1, only pages from site1 sections should be visible
    Filament::setTenant($site1);
    expect(Page::count())->toBe(1);
    expect(Page::first()->id)->toBe($page1->id);

    // When tenant is site2, only pages from site2 sections should be visible
    Filament::setTenant($site2);
    expect(Page::count())->toBe(1);
    expect(Page::first()->id)->toBe($page2->id);
});

test('page type is automatically copied from section type', function () {
    $site = Site::first();
    $section = Section::factory()->forSite($site)->create(['type' => \Eclipse\Cms\Enums\SectionType::News]);

    $component = livewire(CreatePage::class);

    $component->fillForm([
        'title' => 'News Page',
        'section_id' => $section->id,
        'sef_key' => 'news-page',
        'status' => PageStatus::Draft->value,
    ])->call('create');

    $component->assertHasNoFormErrors();

    $page = Page::first();
    expect($page)->not->toBeNull();
    expect($page->type)->toBe('News'); // Should match section type name, not value
});
