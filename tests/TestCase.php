<?php

namespace Tests;

use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Workbench\App\Models\Site;
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

        // Override config for testing
        config(['eclipse-cms.tenancy.model' => 'Workbench\\App\\Models\\Site']);

        // Disable Scout during tests
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
     * Set up default "super admin" user and tenant (site)
     */
    protected function set_up_super_admin_and_tenant(): self
    {
        // Ensure we have at least one site
        $site = Site::first();
        if (! $site) {
            $site = Site::factory()->create(['is_default' => true]);
        }

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->sites()->attach($site);

        // Create super_admin role and assign to user
        $superAdminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);

        // Give super admin all CMS permissions
        $superAdminRole->givePermissionTo([
            'view_any_section',
            'view_section',
            'create_section',
            'update_section',
            'delete_section',
            'view_any_page',
            'view_page',
            'create_page',
            'update_page',
            'delete_page',
        ]);

        $this->superAdmin->assignRole('super_admin');

        $this->actingAs($this->superAdmin);

        if ($site) {
            Filament::setTenant($site);
        }

        return $this;
    }

    /**
     * Set up a common user with no roles or permissions
     */
    protected function set_up_common_user_and_tenant(): self
    {
        // Ensure we have at least one site
        $site = Site::first();
        if (! $site) {
            $site = Site::factory()->create(['is_default' => true]);
        }

        $this->user = User::factory()->create();
        $this->user->sites()->attach($site);

        $this->actingAs($this->user);

        if ($site) {
            Filament::setTenant($site);
        }

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
