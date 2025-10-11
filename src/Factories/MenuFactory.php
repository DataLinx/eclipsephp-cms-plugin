<?php

namespace Eclipse\Cms\Factories;

use Eclipse\Cms\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;

class MenuFactory extends Factory
{
    protected $model = Menu::class;

    public function definition(): array
    {
        return [
            'title' => [
                'en' => $this->faker->words(3, true),
                'sl' => $this->faker->words(3, true),
            ],
            'code' => $this->faker->unique()->slug(2),
            'is_active' => $this->faker->boolean(80),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
