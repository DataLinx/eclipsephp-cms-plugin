<?php

namespace Eclipse\Cms\Seeders;

use Eclipse\Cms\Models\Banner;
use Eclipse\Cms\Models\Banner\ImageType;
use Eclipse\Cms\Models\Banner\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $tenantData = $this->getTenantData();

        $position = Position::factory()->create(['code' => 'website', ...$tenantData]);

        $desktop = ImageType::factory()->for($position)->desktop()->create();
        $mobile = ImageType::factory()->for($position)->mobile()->create();

        $banners = [
            ['name' => ['en' => 'Summer Sale', 'sl' => 'Poletna Razprodaja'], 'active' => true, 'new_tab' => false],
            ['name' => ['en' => 'Winter Sale', 'sl' => 'Zimska Razprodaja'], 'active' => true, 'new_tab' => false],
            ['name' => ['en' => 'Special Offer', 'sl' => 'Posebna Ponudba'], 'active' => false, 'new_tab' => true],
        ];

        foreach ($banners as $index => $bannerData) {
            $banner = Banner::factory()->create([
                'position_id' => $position->id,
                'name' => $bannerData['name'],
                'is_active' => $bannerData['active'],
                'new_tab' => $bannerData['new_tab'],
                'sort' => $index + 1,
            ]);

            $this->addImages($banner, $desktop, 'desktop');
            $this->addImages($banner, $mobile, 'mobile');
        }
    }

    private function addImages($banner, $type, $suffix): void
    {
        $bannerName = is_array($banner->name) ? $banner->name['en'] : $banner->name;

        $regularFilename = "banner-{$banner->id}-{$suffix}.png";
        $this->createBannerImage(
            $type->image_width,
            $type->image_height,
            strtoupper($suffix)." - {$bannerName}",
            $regularFilename
        );

        $banner->images()->create([
            'type_id' => $type->id,
            'file' => $regularFilename,
            'image_width' => $type->image_width,
            'image_height' => $type->image_height,
            'is_hidpi' => false,
        ]);

        if ($type->is_hidpi) {
            $hidpiFilename = "banner-{$banner->id}-{$suffix}@2x.png";
            $hidpiWidth = $type->image_width * 2;
            $hidpiHeight = $type->image_height * 2;

            $this->createBannerImage(
                $hidpiWidth,
                $hidpiHeight,
                strtoupper($suffix)." @2x - {$bannerName}",
                $hidpiFilename
            );

            $banner->images()->create([
                'type_id' => $type->id,
                'file' => $hidpiFilename,
                'image_width' => $hidpiWidth,
                'image_height' => $hidpiHeight,
                'is_hidpi' => true,
            ]);
        }
    }

    private function getTenantData(): array
    {
        if (config('eclipse-cms.tenancy.enabled')) {
            $tenantModel = config('eclipse-cms.tenancy.model');
            if ($tenantModel && class_exists($tenantModel)) {
                $tenantFK = config('eclipse-cms.tenancy.foreign_key', 'site_id');
                $tenant = $tenantModel::first() ?: $tenantModel::create([
                    'name' => 'Default Site',
                    'domain' => 'localhost',
                ]);
                if ($tenant) {
                    return [$tenantFK => $tenant->id];
                }
            }
        }

        return [];
    }

    protected function createBannerImage(int $width, int $height, string $text, string $filePath): bool
    {
        if (Storage::disk('public')->exists($filePath)) {
            return true;
        }

        try {
            $url = "https://dummyimage.com/{$width}x{$height}/ffffff/000000.png?text=".urlencode($text);
            $context = stream_context_create(['http' => ['timeout' => 10]]);
            $imageData = file_get_contents($url, false, $context);

            if (! $imageData) {
                return false;
            }

            $directory = dirname($filePath);
            if (! Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            return Storage::disk('public')->put($filePath, $imageData);
        } catch (\Exception) {
            return false;
        }
    }
}
