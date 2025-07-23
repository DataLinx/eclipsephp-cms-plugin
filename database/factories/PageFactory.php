<?php

namespace Eclipse\Cms\Factories;

use Eclipse\Cms\Models\Page;
use Eclipse\Cms\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @experimental
 */
class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->word(),
            'short_text' => $this->faker->text(),
            'long_text' => $this->faker->text(),
            'sef_key' => $this->faker->word(),
            'code' => $this->faker->word(),
            'status' => $this->faker->word(),
            'type' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'section_id' => Section::factory(),
        ];
    }
}
