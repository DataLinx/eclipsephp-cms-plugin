<?php

namespace Eclipse\Cms\Seeders;

use Eclipse\Cms\Models\Page;
use Eclipse\Cms\Models\Section;
use Illuminate\Database\Seeder;

class CmsSeeder extends Seeder
{
    public function run(): void
    {
        $sections = Section::factory()
            ->count(3)
            ->create();

        $sections->each(function (Section $section): void {
            Page::factory()
                ->count(3)
                ->forSection($section)
                ->create();
        });
    }
}
