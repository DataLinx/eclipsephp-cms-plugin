<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Workbench\App\Models\Site;
use Workbench\Database\Factories\UserFactory;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test site
        $site = Site::factory()->create([
            'name' => 'Test Site',
            'domain' => 'test.local',
        ]);

        // Create super admin role
        $superAdminRole = Role::create(['name' => 'super_admin']);

        // Create permissions for testing
        $permissions = [
            'view_any_section',
            'view_section',
            'create_section',
            'update_section',
            'delete_section',
            'delete_any_section',
            'force_delete_section',
            'force_delete_any_section',
            'restore_section',
            'restore_any_section',
            'view_any_page',
            'view_page',
            'create_page',
            'update_page',
            'delete_page',
            'delete_any_page',
            'force_delete_page',
            'force_delete_any_page',
            'restore_page',
            'restore_any_page',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Give super admin all permissions
        $superAdminRole->givePermissionTo(Permission::all());

        UserFactory::new()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
