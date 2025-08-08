<?php

namespace Eclipse\Cms\Factories;

use Eclipse\Cms\Enums\SectionType;
use Eclipse\Cms\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SectionFactory extends Factory
{
    protected $model = Section::class;

    public function definition(): array
    {
        $englishName = Str::of($this->faker->words(asText: true))->ucwords();
        $slovenianName = "SI: {$englishName}";

        return [
            'name' => [
                'en' => $englishName,
                'sl' => $slovenianName,
            ],
            'type' => $this->faker->randomElement(SectionType::cases()),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    public function configure()
    {
        return $this->afterMaking(function (Section $section) {
            if (config('eclipse-cms.tenancy.enabled')) {
                $foreignKey = config('eclipse-cms.tenancy.foreign_key');
                $currentValue = $section->getAttribute($foreignKey);

                if (! $currentValue || $currentValue === null) {
                    $class = config('eclipse-cms.tenancy.model');
                    if (class_exists($class)) {
                        $newValue = $class::inRandomOrder()->first()?->id ?? $class::factory()->create()->id;
                        $section->setAttribute($foreignKey, $newValue);
                    }
                }
            }
        });
    }

    public function forSite($site): static
    {
        if (config('eclipse-cms.tenancy.enabled')) {
            return $this->state([
                config('eclipse-cms.tenancy.foreign_key') => $site->id,
            ]);
        }

        return $this;
    }
}
