<?php

namespace Eclipse\Cms\Factories;

use Eclipse\Cms\Enums\PageStatus;
use Eclipse\Cms\Models\Page;
use Eclipse\Cms\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        $englishTitle = $this->faker->sentence(3);
        $slovenianTitle = "SI: {$englishTitle}";

        $englishShortText = $this->faker->text(200);
        $slovenianShortText = "SI: {$englishShortText}";

        $englishLongText = $this->faker->text(500);
        $slovenianLongText = "SI: {$englishLongText}";

        $slug = $this->faker->slug();

        return [
            'title' => [
                'en' => $englishTitle,
                'sl' => $slovenianTitle,
            ],
            'short_text' => [
                'en' => $englishShortText,
                'sl' => $slovenianShortText,
            ],
            'long_text' => [
                'en' => $englishLongText,
                'sl' => $slovenianLongText,
            ],
            'sef_key' => [
                'en' => $slug,
                'sl' => "{$slug}-si",
            ],
            'code' => $this->faker->unique()->word(),
            'status' => $this->faker->randomElement([PageStatus::Draft, PageStatus::Published]),
            'type' => 'page',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    public function configure()
    {
        return $this->afterMaking(function (Page $page) {
            if (! $page->section_id) {
                $page->section_id = Section::factory()->create()->id;
            }
        });
    }

    public function forSection($section): static
    {
        return $this->state([
            'section_id' => $section->id,
        ]);
    }
}
