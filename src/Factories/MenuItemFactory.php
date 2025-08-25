<?php

namespace Eclipse\Cms\Factories;

use Eclipse\Cms\Enums\MenuItemType;
use Eclipse\Cms\Models\Menu;
use Eclipse\Cms\Models\Menu\Item;
use Eclipse\Cms\Models\Page;
use Eclipse\Cms\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

class MenuItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(MenuItemType::cases());

        return [
            'label' => [
                'en' => $this->faker->words(2, true),
                'sl' => $this->faker->words(2, true),
            ],
            'menu_id' => Menu::factory(),
            'parent_id' => null,
            'type' => $type,
            'linkable_class' => $this->getLinkableClass($type),
            'linkable_id' => null,
            'custom_url' => $type === MenuItemType::CustomUrl ? $this->faker->url() : null,
            'new_tab' => $this->faker->boolean(30),
            'is_active' => $this->faker->boolean(85),
            'sort' => $this->faker->numberBetween(0, 100),
        ];
    }

    public function linkableToPage(): static
    {
        return $this->state(function (array $attributes) {
            $page = Page::factory()->create();

            return [
                'type' => MenuItemType::Linkable,
                'linkable_class' => Page::class,
                'linkable_id' => $page->id,
                'custom_url' => null,
            ];
        });
    }

    public function linkableToSection(): static
    {
        return $this->state(function (array $attributes) {
            $section = Section::factory()->create();

            return [
                'type' => MenuItemType::Linkable,
                'linkable_class' => Section::class,
                'linkable_id' => $section->id,
                'custom_url' => null,
            ];
        });
    }

    public function customUrl(?string $url = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => MenuItemType::CustomUrl,
            'linkable_class' => null,
            'linkable_id' => null,
            'custom_url' => $url ?? $this->faker->url(),
        ]);
    }

    public function group(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => MenuItemType::Group,
            'linkable_class' => null,
            'linkable_id' => null,
            'custom_url' => null,
        ]);
    }

    public function childOf(Item $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
            'menu_id' => $parent->menu_id,
        ]);
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

    private function getLinkableClass(MenuItemType $type): ?string
    {
        if ($type !== MenuItemType::Linkable) {
            return null;
        }

        $classes = [Page::class, Section::class];

        return $this->faker->randomElement($classes);
    }
}
