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
        // Always show errors when testing
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        parent::setUp();

        $this->withoutVite();

        config(['eclipse-cms.tenancy.enabled' => false]);
        config(['eclipse-cms.tenancy.model' => 'Workbench\\App\\Models\\Site']);
        config(['eclipse-cms.tenancy.foreign_key' => 'site_id']);

        config(['scout.driver' => null]);
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
        $this->superAdmin = User::factory()->make();
        $this->superAdmin->assignRole('super_admin')->save();
        $this->actingAs($this->superAdmin);

        return $this;
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
