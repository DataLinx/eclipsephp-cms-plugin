<?php

namespace Eclipse\Cms\Seeders;

use Eclipse\Cms\Models\Section;
use Illuminate\Database\Seeder;

class CmsSeeder extends Seeder
{
    public function run(): void
    {
        Section::factory()
            ->count(3)
            ->create();
    }
}
