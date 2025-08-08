<?php

namespace Tests\Feature\Filament\Resources;

use Eclipse\Cms\Admin\Filament\Resources\SectionResource;
use Eclipse\Cms\Models\Section;
use Filament\Actions\DeleteAction;
use Livewire\Livewire;
use Tests\TestCase;

class SectionResourceTest extends TestCase
{
    public function test_authorized_access_can_view_sections_list(): void
    {
        $this->migrate()
            ->set_up_super_admin_and_tenant();

        $response = $this->get(SectionResource::getUrl('index'));

        $response->assertSuccessful();
    }

    public function test_create_section_screen_can_be_rendered(): void
    {
        $this->migrate()
            ->set_up_super_admin_and_tenant();

        $response = $this->get(SectionResource::getUrl('create'));

        $response->assertSuccessful();
    }

    public function test_section_form_validation_works(): void
    {
        $this->migrate()
            ->set_up_super_admin_and_tenant();

        Livewire::test(SectionResource\Pages\CreateSection::class)
            ->fillForm([
                'name' => '',
                'type' => 'pages',
            ])
            ->call('create')
            ->assertHasFormErrors(['name']);
    }

    public function test_section_can_be_created_through_form(): void
    {
        $this->migrate()
            ->set_up_super_admin_and_tenant();

        $newData = [
            'name.en' => 'Test Section',
            'name.sl' => 'Test Sekcija',
            'type' => 'pages',
        ];

        Livewire::test(SectionResource\Pages\CreateSection::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('cms_sections', [
            'type' => 'pages',
        ]);
    }

    public function test_section_can_be_updated(): void
    {
        $this->migrate()
            ->set_up_super_admin_and_tenant();

        $section = Section::factory()->create();

        $newData = [
            'name.en' => 'Updated Section',
            'name.sl' => 'Posodobljena Sekcija',
            'type' => 'pages',
        ];

        Livewire::test(SectionResource\Pages\EditSection::class, [
            'record' => $section->getRouteKey(),
        ])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue(true);
    }

    public function test_section_can_be_deleted(): void
    {
        $this->migrate()
            ->set_up_super_admin_and_tenant();

        $section = Section::factory()->create();

        Livewire::test(SectionResource\Pages\EditSection::class, [
            'record' => $section->getRouteKey(),
        ])
            ->callAction(DeleteAction::class);

        $this->assertSoftDeleted($section);
    }

    public function test_unauthorized_access_can_be_prevented(): void
    {
        $this->migrate()
            ->set_up_user_without_permissions();

        $response = $this->get(SectionResource::getUrl('index'));

        $response->assertForbidden();
    }

    public function test_user_with_create_permission_can_create_sections(): void
    {
        $this->migrate()
            ->set_up_user_with_permissions(['view_any_section', 'create_section']);

        $response = $this->get(SectionResource::getUrl('create'));

        $response->assertSuccessful();
    }

    public function test_user_with_update_permission_can_edit_sections(): void
    {
        $this->migrate()
            ->set_up_user_with_permissions(['view_any_section', 'view_section', 'update_section']);

        $section = Section::factory()->create();

        $response = $this->get(SectionResource::getUrl('edit', [
            'record' => $section,
        ]));

        $response->assertSuccessful();
    }

    public function test_user_with_delete_permission_can_delete_sections(): void
    {
        $this->migrate()
            ->set_up_user_with_permissions(['view_any_section', 'view_section', 'update_section', 'delete_section']);

        $section = Section::factory()->create();

        Livewire::test(SectionResource\Pages\EditSection::class, [
            'record' => $section->getRouteKey(),
        ])
            ->callAction('delete');

        $this->assertSoftDeleted($section);
    }
}
