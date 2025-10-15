<?php

namespace Tests;

use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Workbench\App\Models\User;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase, WithWorkbench;

    protected ?User $superAdmin = null;

    protected ?User $user = null;

    protected function setUp(): void
    {
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        parent::setUp();

        $this->withoutVite();

        config(['eclipse-cms.tenancy.enabled' => false]);
        config(['eclipse-cms.tenancy.model' => 'Workbench\\App\\Models\\Site']);
        config(['eclipse-cms.tenancy.foreign_key' => 'site_id']);

        config(['scout.driver' => null]);
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:0qAvnB4fU0hiVsd01U1b/GljkPRLBS50IQ7I4DS7fxI=');
    }

    /**
     * Run database migrations
     */
    protected function migrate(): self
    {
        $this->artisan('migrate');

        return $this;
    }

    /**
     * Set up default "super admin" user
     */
    protected function setUpSuperAdmin(): self
    {
        $this->migrate();
        $this->superAdmin = User::factory()->create();

        $this->createAllPermissions();

        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $role->syncPermissions(Permission::all());

        $this->superAdmin->assignRole('super_admin');

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->superAdmin, 'web');

        return $this;
    }

    protected function createAllPermissions(): void
    {
        $permissions = [
            'view_any_menu',
            'view_menu',
            'create_menu',
            'update_menu',
            'delete_menu',
            'delete_any_menu',
            'force_delete_menu',
            'force_delete_any_menu',
            'restore_menu',
            'restore_any_menu',
            'view_any_position',
            'view_position',
            'create_position',
            'update_position',
            'delete_position',
            'delete_any_position',
            'force_delete_position',
            'force_delete_any_position',
            'restore_position',
            'restore_any_position',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }

    /**
     * Set up a common user with no roles or permissions
     */
    protected function setUpCommonUser(): self
    {
        $this->migrate();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        return $this;
    }

    protected function setUpUserWithoutPermissions(): self
    {
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
