<?php

namespace Eclipse\Cms\Factories;

use Eclipse\Cms\Models\Banner\Image;
use Illuminate\Database\Eloquent\Factories\Factory;

class BannerImageFactory extends Factory
{
    protected $model = Image::class;

    public function definition(): array
    {
        $sampleImages = [
            'banners/summer-sale-desktop.jpg',
            'banners/winter-collection-mobile.jpg',
            'banners/spring-deals-tablet.jpg',
            'banners/black-friday-hero.jpg',
            'banners/new-arrivals-sidebar.jpg',
            'banners/limited-offer-footer.jpg',
            'banners/featured-product-square.jpg',
            'banners/special-promo-wide.jpg',
            'banners/holiday-sale-banner.jpg',
            'banners/end-season-deals.jpg',
        ];

        return [
            'file' => $this->faker->randomElement($sampleImages),
            'image_width' => $this->faker->randomElement([400, 800, 1200, 1920]),
            'image_height' => $this->faker->randomElement([200, 400, 600, 800]),
            'is_hidpi' => $this->faker->boolean(30),
        ];
    }

    public function desktop(): static
    {
        return $this->state(fn (array $attributes): array => [
            'file' => 'banners/desktop-'.$this->faker->slug().'.jpg',
            'image_width' => 1920,
            'image_height' => 600,
            'is_hidpi' => false,
        ]);
    }

    public function mobile(): static
    {
        return $this->state(fn (array $attributes): array => [
            'file' => 'banners/mobile-'.$this->faker->slug().'.jpg',
            'image_width' => 800,
            'image_height' => 400,
            'is_hidpi' => true,
        ]);
    }

    public function tablet(): static
    {
        return $this->state(fn (array $attributes): array => [
            'file' => 'banners/tablet-'.$this->faker->slug().'.jpg',
            'image_width' => 1024,
            'image_height' => 500,
            'is_hidpi' => false,
        ]);
    }

    public function square(): static
    {
        return $this->state(fn (array $attributes): array => [
            'file' => 'banners/square-'.$this->faker->slug().'.jpg',
            'image_width' => 400,
            'image_height' => 400,
            'is_hidpi' => false,
        ]);
    }

    public function hidpi(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_hidpi' => true,
            'image_width' => $attributes['image_width'] * 2,
            'image_height' => $attributes['image_height'] * 2,
        ]);
    }
}
