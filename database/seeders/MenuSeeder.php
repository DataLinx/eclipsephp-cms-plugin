<?php

namespace Eclipse\Cms\Seeders;

use Eclipse\Cms\Models\Menu;
use Eclipse\Cms\Models\Menu\Item;
use Eclipse\Cms\Models\Page;
use Eclipse\Cms\Models\Section;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $this->createMainMenu();
        $this->createFooterMenu();
    }

    private function getTenantData(): array
    {
        if (config('eclipse-cms.tenancy.enabled')) {
            $tenantModel = config('eclipse-cms.tenancy.model');
            if ($tenantModel && class_exists($tenantModel)) {
                $tenantFK = config('eclipse-cms.tenancy.foreign_key', 'site_id');

                $tenant = $tenantModel::first();
                if (! $tenant) {
                    $tenant = $tenantModel::create([
                        'name' => 'Default Site',
                        'domain' => 'localhost',
                    ]);
                }

                if ($tenant) {
                    return [$tenantFK => $tenant->id];
                }
            }
        }

        return [];
    }

    private function createMainMenu(): void
    {
        $menu = Menu::factory()->create(array_merge([
            'title' => [
                'en' => 'Main Navigation',
                'sl' => 'Glavna Navigacija',
            ],
            'code' => 'main',
            'is_active' => true,
        ], $this->getTenantData()));

        $homeSection = Section::first() ?: Section::factory()->create([
            'name' => [
                'en' => 'Home',
                'sl' => 'Domov',
            ],
        ]);

        $aboutSection = Section::first() ?: Section::factory()->create([
            'name' => [
                'en' => 'About',
                'sl' => 'O nas',
            ],
        ]);

        $homeItem = Item::factory()->linkableToSection()->create([
            'label' => [
                'en' => 'Home',
                'sl' => 'Domov',
            ],
            'menu_id' => $menu->id,
            'linkable_id' => $homeSection->id,
            'is_active' => true,
            'sort' => 1,
        ]);

        $aboutItem = Item::factory()->linkableToSection()->create([
            'label' => [
                'en' => 'About Us',
                'sl' => 'O nas',
            ],
            'menu_id' => $menu->id,
            'linkable_id' => $aboutSection->id,
            'is_active' => true,
            'sort' => 2,
        ]);

        $servicesGroup = Item::factory()->group()->create([
            'label' => [
                'en' => 'Our Services',
                'sl' => 'Naše Storitve',
            ],
            'menu_id' => $menu->id,
            'is_active' => true,
            'sort' => 3,
        ]);

        if (Page::count() > 0) {
            $servicePage = Page::first();
            Item::factory()->linkableToPage()->childOf($servicesGroup)->create([
                'label' => [
                    'en' => 'Web Development Services',
                    'sl' => 'Storitve Spletnega Razvoja',
                ],
                'linkable_id' => $servicePage->id,
                'is_active' => true,
                'sort' => 1,
            ]);
        }

        Item::factory()->customUrl('https://support.example.com')->childOf($servicesGroup)->create([
            'label' => [
                'en' => 'Customer Support',
                'sl' => 'Podpora Strankam',
            ],
            'new_tab' => true,
            'is_active' => true,
            'sort' => 2,
        ]);

        Item::factory()->customUrl('/contact')->create([
            'label' => [
                'en' => 'Contact Us',
                'sl' => 'Kontakt',
            ],
            'menu_id' => $menu->id,
            'is_active' => true,
            'sort' => 4,
        ]);
    }

    private function createFooterMenu(): void
    {
        $menu = Menu::factory()->create(array_merge([
            'title' => [
                'en' => 'Footer Links',
                'sl' => 'Povezave v Nogi',
            ],
            'code' => 'footer',
            'is_active' => true,
        ], $this->getTenantData()));

        Item::factory()->customUrl('/privacy')->create([
            'label' => [
                'en' => 'Privacy Policy',
                'sl' => 'Pravilnik o Zasebnosti',
            ],
            'menu_id' => $menu->id,
            'is_active' => true,
            'sort' => 1,
        ]);

        Item::factory()->customUrl('/terms')->create([
            'label' => [
                'en' => 'Terms of Service',
                'sl' => 'Pogoji Uporabe',
            ],
            'menu_id' => $menu->id,
            'is_active' => true,
            'sort' => 2,
        ]);

        if (Section::count() > 1) {
            $section = Section::skip(1)->first();
            Item::factory()->linkableToSection()->create([
                'label' => [
                    'en' => 'Latest News',
                    'sl' => 'Najnovice',
                ],
                'menu_id' => $menu->id,
                'linkable_id' => $section->id,
                'is_active' => true,
                'sort' => 3,
            ]);
        }
    }
}
