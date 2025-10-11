<?php

namespace Eclipse\Cms\Factories;

use Eclipse\Cms\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

class BannerFactory extends Factory
{
    protected $model = Banner::class;

    public function definition(): array
    {
        $names = [
            'en' => ['Summer Sale', 'Winter Sale', 'Special Offer', 'New Arrivals', 'Featured Deal'],
            'sl' => ['Poletna Razprodaja', 'Zimska Razprodaja', 'Posebna Ponudba', 'Nove Stvari', 'Izpostavljena Ponudba'],
        ];

        $nameIndex = $this->faker->numberBetween(0, count($names['en']) - 1);

        return [
            'name' => [
                'en' => $names['en'][$nameIndex],
                'sl' => $names['sl'][$nameIndex],
            ],
            'link' => 'https://example.com/'.$this->faker->slug(2),
            'is_active' => $this->faker->boolean(80),
            'new_tab' => $this->faker->boolean(30),
            'sort' => $this->faker->numberBetween(1, 10),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    public function newTab(): static
    {
        return $this->state(fn (array $attributes): array => [
            'new_tab' => true,
        ]);
    }
}
