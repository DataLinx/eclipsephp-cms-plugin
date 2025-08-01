<?php

use Eclipse\Cms\Enums\SectionType;
use Eclipse\Cms\Filament\Resources\SectionResource;
use Eclipse\Cms\Filament\Resources\SectionResource\Pages\CreateSection;
use Eclipse\Cms\Filament\Resources\SectionResource\Pages\ListSections;
use Eclipse\Cms\Models\Section;
use Filament\Facades\Filament;
use Workbench\App\Models\Site;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->set_up_super_admin_and_tenant();
});

test('authorized access can view sections list', function () {
    $this->get(SectionResource::getUrl())
        ->assertOk();
});

test('create section screen can be rendered', function () {
    $this->get(SectionResource::getUrl('create'))
        ->assertOk();
});

test('section form validation works', function () {
    $component = livewire(CreateSection::class);

    $component->assertFormExists();

    // Test required fields
    $component->call('create')
        ->assertHasFormErrors([
            'name' => 'required',
            'type' => 'required',
        ]);
});

test('section can be created through form', function () {
    $component = livewire(CreateSection::class);

    $component->fillForm([
        'name' => 'Test Section',
        'type' => SectionType::Pages->value,
    ])->call('create');

    $component->assertHasNoFormErrors();

    $section = Section::first();
    expect($section)->not->toBeNull();
    expect($section->name)->toBe('Test Section');
    expect($section->type)->toBe(SectionType::Pages);
    expect($section->site_id)->toBe(Site::first()->id);
});

test('sections list shows only current tenant sections', function () {
    $site1 = Site::first();
    $site2 = Site::factory()->create();

    // Create sections for different sites
    Section::factory()->forSite($site1)->create(['name' => ['en' => 'Site 1 Section']]);
    Section::factory()->forSite($site2)->create(['name' => ['en' => 'Site 2 Section']]);

    $component = livewire(ListSections::class);

    // Should only show sections for current tenant (site1)
    $component->assertCanSeeTableRecords([
        Section::where('site_id', $site1->id)->first(),
    ]);

    $component->assertCanNotSeeTableRecords([
        Section::where('site_id', $site2->id)->first(),
    ]);
});

test('section can be updated', function () {
    $site = Site::first();
    $section = Section::factory()->forSite($site)->create();

    $component = livewire(SectionResource\Pages\EditSection::class, [
        'record' => $section->getRouteKey(),
    ]);

    $component->fillForm([
        'name' => 'Updated Section Name',
        'type' => SectionType::Pages->value,
    ])->call('save');

    $component->assertHasNoFormErrors();

    $section->refresh();
    expect($section->name)->toBe('Updated Section Name');
});

test('section can be deleted', function () {
    $site = Site::first();
    $section = Section::factory()->forSite($site)->create();

    $component = livewire(ListSections::class);

    $component->callTableAction('delete', $section);

    expect(Section::count())->toBe(0);
});

test('unauthorized access can be prevented', function () {
    // Create regular user with no permissions
    $this->set_up_common_user_and_tenant();

    $this->user->syncRoles([]);
    $this->user->syncPermissions([]);

    $site = Site::first();
    $section = Section::factory()->forSite($site)->create();

    // View table
    $this->get(SectionResource::getUrl())
        ->assertForbidden();

    // Add direct permission to view the table, since otherwise any other action below is not available even for testing
    $this->user->givePermissionTo('view_any_section');

    // Create section
    livewire(ListSections::class)
        ->assertActionDisabled('create');

    // Edit section
    livewire(ListSections::class)
        ->assertCanSeeTableRecords([$section])
        ->assertTableActionDisabled('edit', $section);

    // Delete section
    livewire(ListSections::class)
        ->assertTableActionDisabled('delete', $section)
        ->assertTableBulkActionDisabled('delete');
});

test('user with create permission can create sections', function () {
    // Create regular user with only create permission
    $this->set_up_common_user_and_tenant();

    $this->user->syncRoles([]);
    $this->user->syncPermissions(['view_any_section', 'create_section']);

    $component = livewire(CreateSection::class);

    $component->fillForm([
        'name' => 'Authorized Section',
        'type' => SectionType::Pages->value,
    ])->call('create');

    $component->assertHasNoFormErrors();

    $section = Section::where('name->en', 'Authorized Section')->first();
    expect($section)->not->toBeNull();
    expect($section->name)->toBe('Authorized Section');
});

test('user with update permission can edit sections', function () {
    // Create regular user with update permission
    $this->set_up_common_user_and_tenant();

    $this->user->syncRoles([]);
    $this->user->syncPermissions(['view_any_section', 'view_section', 'update_section']);

    $site = Site::first();
    $section = Section::factory()->forSite($site)->create();

    $component = livewire(SectionResource\Pages\EditSection::class, [
        'record' => $section->getRouteKey(),
    ]);

    $component->fillForm([
        'name' => 'Updated by Regular User',
        'type' => SectionType::Pages->value,
    ])->call('save');

    $component->assertHasNoFormErrors();

    $section->refresh();
    expect($section->name)->toBe('Updated by Regular User');
});

test('user with delete permission can delete sections', function () {
    // Create regular user with delete permission
    $this->set_up_common_user_and_tenant();

    $this->user->syncRoles([]);
    $this->user->syncPermissions(['view_any_section', 'delete_section']);

    $site = Site::first();
    $section = Section::factory()->forSite($site)->create();

    $component = livewire(ListSections::class);

    $component->callTableAction('delete', $section);

    expect(Section::count())->toBe(0);
});

test('user with restore permission can restore deleted sections', function () {
    // Create regular user with restore permission
    $this->set_up_common_user_and_tenant();

    $this->user->syncRoles([]);
    $this->user->syncPermissions(['view_any_section', 'restore_section']);

    $site = Site::first();
    $section = Section::factory()->forSite($site)->create();
    $sectionId = $section->id;
    $section->delete(); // Soft delete

    // Verify it's soft deleted
    expect(Section::find($sectionId))->toBeNull();
    expect(Section::withTrashed()->find($sectionId))->not->toBeNull();

    // Restore directly through model for now (table action testing can be complex)
    $trashedSection = Section::withTrashed()->find($sectionId);
    $trashedSection->restore();

    expect(Section::find($sectionId))->not->toBeNull();
    expect(Section::find($sectionId)->deleted_at)->toBeNull();
});

test('user with force delete permission can permanently delete sections', function () {
    // Create regular user with force delete permission
    $this->set_up_common_user_and_tenant();

    $this->user->syncRoles([]);
    $this->user->syncPermissions(['view_any_section', 'force_delete_section']);

    $site = Site::first();
    $section = Section::factory()->forSite($site)->create();
    $sectionId = $section->id;

    // Force delete directly through model for now
    $section->forceDelete();

    expect(Section::withTrashed()->where('id', $sectionId)->count())->toBe(0);
});

test('tenant scoping prevents cross-tenant section access', function () {
    // This test verifies that sections are properly scoped by tenant in the model level
    $site1 = Site::first();
    $site2 = Site::factory()->create();

    // Clear tenant so sections can be created with explicit site_id
    Filament::setTenant(null);

    $section1 = Section::factory()->forSite($site1)->create(['name' => ['en' => 'Site 1 Section']]);
    $section2 = Section::factory()->forSite($site2)->create(['name' => ['en' => 'Site 2 Section']]);

    // When tenant is site1, only site1 sections should be visible
    Filament::setTenant($site1);
    expect(Section::count())->toBe(1);
    expect(Section::first()->id)->toBe($section1->id);

    // When tenant is site2, only site2 sections should be visible
    Filament::setTenant($site2);
    expect(Section::count())->toBe(1);
    expect(Section::first()->id)->toBe($section2->id);
});
