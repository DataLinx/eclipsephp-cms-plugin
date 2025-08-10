<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Workbench\App\Models\User;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase, WithWorkbench;

    protected ?User $superAdmin = null;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        config(['eclipse-cms.tenancy.enabled' => false]);
        config(['eclipse-cms.tenancy.model' => 'Workbench\\App\\Models\\Site']);
        config(['eclipse-cms.tenancy.foreign_key' => 'site_id']);
        config(['app.key' => 'base64:'.base64_encode('12345678901234567890123456789012')]);

        // Disable Scout during tests
        config(['scout.driver' => null]);
    }

    protected function migrate(): self
    {
        $this->artisan('migrate');

        return $this;
    }

    protected function setUpSuperAdmin(): self
    {
        $this->migrate();
        $this->superAdmin = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $permissions = [
            'view_any_page', 'view_page', 'create_page', 'update_page', 'delete_page', 'restore_page', 'force_delete_page',
            'view_any_section', 'view_section', 'create_section', 'update_section', 'delete_section', 'restore_section', 'force_delete_section',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $role->syncPermissions($permissions);
        $this->superAdmin->assignRole($role);
        $this->actingAs($this->superAdmin);

        return $this;
    }

    protected function setUpCommonUserAndTenant(): self
    {
        $this->migrate();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        return $this;
    }

    protected function setUpUserWithoutPermissions(): self
    {
        $this->migrate();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        return $this;
    }

    protected function setUpUserWithPermissions(array $permissions): self
    {
        $this->migrate();
        $this->user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'test_role', 'guard_name' => 'web']);

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $role->syncPermissions($permissions);
        $this->user->assignRole('test_role');
        $this->actingAs($this->user);

        return $this;
    }

    public function ignorePackageDiscoveriesFrom()
    {
        return [
            // A list of packages that should not be auto-discovered when running tests
            'laravel/telescope',
        ];
    }
}
