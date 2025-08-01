<?php

namespace Eclipse\Cms\Seeders;

use Eclipse\Cms\Models\Page;
use Eclipse\Cms\Models\Section;
use Eclipse\Core\Models\Site;
use Illuminate\Database\Seeder;

class CmsSeeder extends Seeder
{
    public function run(): void
    {
        $sites = Site::all();

        if ($sites->isEmpty()) {
            $sites = collect([Site::factory()->create()]);
        }

        foreach ($sites as $site) {
            $sections = Section::factory()
                ->count(3)
                ->forSite($site)
                ->create([
                    'name' => [
                        'en' => 'Information',
                        'sl' => 'Informacije',
                    ],
                ]);

            $sections->each(function (Section $section) {
                Page::factory()
                    ->count(3)
                    ->forSection($section)
                    ->create();
            });
        }
    }
}
