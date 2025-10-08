<?php

namespace Eclipse\Cms\Factories;

use Eclipse\Cms\Models\Banner\ImageType;
use Illuminate\Database\Eloquent\Factories\Factory;

class BannerImageTypeFactory extends Factory
{
    protected $model = ImageType::class;

    public function definition(): array
    {
        return [
            'name' => [
                'en' => 'Desktop',
                'sl' => 'Namizje',
            ],
            'code' => 'desktop',
            'image_width' => 1200,
            'image_height' => 400,
            'is_hidpi' => false,
        ];
    }

    public function desktop(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => [
                'en' => 'Desktop',
                'sl' => 'Namizje',
            ],
            'code' => 'desktop',
            'image_width' => 1200,
            'image_height' => 400,
            'is_hidpi' => false,
        ]);
    }

    public function mobile(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => [
                'en' => 'Mobile',
                'sl' => 'Mobilni',
            ],
            'code' => 'mobile',
            'image_width' => 800,
            'image_height' => 400,
            'is_hidpi' => true,
        ]);
    }

    public function hidpi(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_hidpi' => true,
        ]);
    }
}
