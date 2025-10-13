<?php

namespace Eclipse\Cms\Admin\Filament\Resources\MenuResource\Pages;

use Eclipse\Cms\Admin\Filament\Resources\MenuResource;
use Eclipse\Cms\Enums\MenuItemType;
use Eclipse\Cms\Models\Menu;
use Eclipse\Cms\Models\Menu\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use SolutionForest\FilamentTree\Concern\TreeRecords\Translatable;
use SolutionForest\FilamentTree\Resources\Pages\TreePage as BasePage;

class SortMenuItems extends BasePage
{
    use Translatable;

    protected static string $resource = MenuResource::class;

    protected static int $maxDepth = 5;

    protected function getMenu(): Menu
    {
        return Menu::findOrFail(request()->route('record'));
    }

    public function getTitle(): string
    {
        return 'Sort Menu Items';
    }

    public function getSubheading(): ?string
    {
        return "Drag and drop to reorder menu items for: {$this->getMenu()->title}";
    }

    protected function getTreeQuery(): Builder
    {
        return Item::query()
            ->with('children')
            ->where('menu_id', $this->getMenu()->id);
    }

    public function getTreeRecordTitle(?Model $record = null): string
    {
        if (! $record) {
            return '';
        }

        return $record->label;
    }

    public function getTreeRecordIcon(?Model $record = null): ?string
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
            LocaleSwitcher::make(),
        ];
    }

    protected function hasDeleteAction(): bool
    {
        return false;
    }

    protected function hasEditAction(): bool
    {
        return false;
    }

    protected function hasViewAction(): bool
    {
        return false;
    }
}
