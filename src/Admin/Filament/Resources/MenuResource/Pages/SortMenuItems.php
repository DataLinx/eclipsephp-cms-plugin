<?php

namespace Eclipse\Cms\Admin\Filament\Resources\MenuResource\Pages;

use Eclipse\Cms\Admin\Filament\Resources\MenuResource;
use Eclipse\Cms\Enums\MenuItemType;
use Eclipse\Cms\Models\Menu\Item;
use Filament\Actions;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\FilamentTree\Components\Tree;
use SolutionForest\FilamentTree\Concern\InteractWithTree;
use SolutionForest\FilamentTree\Contract\HasTree;

class SortMenuItems extends Page implements HasTree
{
    use InteractsWithRecord, InteractWithTree, Translatable;

    protected static string $resource = MenuResource::class;

    protected static string $view = 'filament-tree::pages.tree';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getTitle(): string
    {
        return 'Sort Menu Items';
    }

    public function getSubheading(): ?string
    {
        return "Drag and drop to reorder menu items for: {$this->record->title}";
    }

    protected function getTreeQuery(): Builder
    {
        return Item::query()->where('menu_id', $this->record->id);
    }

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();

        $breadcrumbs = [
            $resource::getUrl() => $resource::getBreadcrumb(),
            $resource::getUrl('edit', [
                'record' => $this->record->id,
            ]) => "Edit {$this->record->title}",
            ...(filled($breadcrumb = $this->getBreadcrumb()) ? [$breadcrumb] : []),
        ];

        if (filled($cluster = static::getCluster())) {
            return $cluster::unshiftClusterBreadcrumbs($breadcrumbs);
        }

        return $breadcrumbs;
    }

    public function getModel(): string
    {
        return Item::class;
    }

    public function getMaxDepth(): int
    {
        return 5;
    }

    public static function tree(Tree $tree): Tree
    {
        return $tree;
    }

    protected function getTreeRecordIcon(?Model $record = null): ?string
    {
        if (! $record) {
            return 'heroicon-o-bars-3';
        }

        return match ($record->type) {
            MenuItemType::Group => 'heroicon-o-folder',
            MenuItemType::Linkable => 'heroicon-o-link',
            MenuItemType::CustomUrl => 'heroicon-o-globe-alt',
            default => 'heroicon-o-bars-3',
        };
    }

    protected function getActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }
}
