<?php

namespace Eclipse\Cms\Factories;

use Eclipse\Cms\Models\Banner\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

class BannerPositionFactory extends Factory
{
    protected $model = Position::class;

    public function definition(): array
    {
        return [
            'name' => [
                'en' => 'Website Banners',
                'sl' => 'Spletni Bannerji',
            ],
            'code' => 'website',
        ];
    }
}
