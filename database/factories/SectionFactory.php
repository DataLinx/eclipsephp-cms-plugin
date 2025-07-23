<?php

namespace Eclipse\Cms\Factories;

use Eclipse\Cms\Enums\SectionType;
use Eclipse\Cms\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SectionFactory extends Factory
{
    protected $model = Section::class;

    public function definition(): array
    {
        $attrs = [
            'name' => Str::of($this->faker->words(asText: true))->ucwords(),
            'type' => $this->faker->randomElement(Arr::pluck(SectionType::cases(), 'name')),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        if (config('eclipse-cms.tenancy.enabled') && empty($attrs[config('eclipse-cms.tenancy.foreign_key')])) {
            $class = config('eclipse-cms.tenancy.model');
            $attrs[config('eclipse-cms.tenancy.foreign_key')] = $class::inRandomOrder()->first()?->id ?? $class::factory()->create()->id;
        }

        return $attrs;
    }
}
