<?php

namespace Eclipse\Cms\Seeders;

use Eclipse\Cms\Models\Page;
use Eclipse\Cms\Models\Section;
use Illuminate\Database\Seeder;

class CmsSeeder extends Seeder
{
    public function run(): void
    {
        if (config('eclipse-cms.tenancy.enabled')) {
            $tenantModel = config('eclipse-cms.tenancy.model');
            $tenants = $tenantModel::all();

            if ($tenants->isEmpty()) {
                $tenants = collect([$tenantModel::factory()->create()]);
            }

            $tenants->each(function ($tenant): void {
                $this->seedForTenant($tenant);
            });
        } else {
            $this->seedWithoutTenancy();
        }
    }

    protected function seedForTenant($tenant): void
    {
        $sections = Section::factory()
            ->forSite($tenant)
            ->count(5)
            ->create();

        $sections->each(function (Section $section): void {
            Page::factory()
                ->count(rand(2, 5))
                ->forSection($section)
                ->create();
        });

        $this
            ->call(BannerSeeder::class)
            ->call(MenuSeeder::class);
    }

    protected function seedWithoutTenancy(): void
    {
        $sections = Section::factory()
            ->count(5)
            ->create();

        $sections->each(function (Section $section): void {
            Page::factory()
                ->count(rand(2, 5))
                ->forSection($section)
                ->create();
        });

        $this
            ->call(BannerSeeder::class)
            ->call(MenuSeeder::class);
    }
}
