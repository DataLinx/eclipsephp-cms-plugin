<?php

namespace Eclipse\Cms\Services;

use Eclipse\Common\Foundation\Plugins\HasLinkables;
use Filament\Facades\Filament;
use Illuminate\Support\Collection;

class LinkableDiscoveryService
{
    protected static ?Collection $linkables = null;

    public static function discoverLinkables(): Collection
    {
        if (static::$linkables !== null) {
            return static::$linkables;
        }

        static::$linkables = collect();

        foreach (Filament::getCurrentPanel()->getPlugins() as $plugin) {
            if ($plugin instanceof HasLinkables) {
                $linkables = $plugin->getLinkables();

                foreach ($linkables as $class => $label) {
                    static::$linkables->put($class, $label);
                }
            }
        }

        return static::$linkables;
    }

    public static function getLinkableOptions(): array
    {
        return static::discoverLinkables()->toArray();
    }

    public static function getLinkableModels(string $linkableClass): Collection
    {
        if (! class_exists($linkableClass)) {
            return collect();
        }

        return $linkableClass::query()
            ->when(method_exists($linkableClass, 'scopeActive'), fn ($query) => $query->active())
            ->get()
            ->mapWithKeys(function ($model) {
                $title = $model->title ?? $model->name ?? $model->label ?? "#{$model->id}";

                return [$model->id => $title];
            });
    }

    public static function clearCache(): void
    {
        static::$linkables = null;
    }
}
