<?php

namespace Eclipse\Cms\Admin\Filament\Resources\MenuItemResource\Pages;

use Eclipse\Cms\Admin\Filament\Resources\MenuItemResource;
use Eclipse\Cms\Enums\MenuItemType;
use Filament\Actions;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\FilamentTree\Concern\TreeRecords\Translatable;
use SolutionForest\FilamentTree\Resources\Pages\TreePage as BasePage;

class SortingMenuItem extends BasePage
{
    use Translatable;

    protected static string $resource = MenuItemResource::class;

    protected static int $maxDepth = 6;

    public function getTitle(): string
    {
        return 'Sorting';
    }

    protected function getActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            $this->getCreateAction()
                ->translateLabel()
                ->label('Create')
                ->icon('heroicon-o-plus-circle'),
        ];
    }

    public function getTreeRecordTitle(?Model $record = null): string
    {
        if (! $record) {
            return '';
        }

        return $record->label;
    }

    protected function hasDeleteAction(): bool
    {
        return true;
    }

    protected function hasEditAction(): bool
    {
        return true;
    }

    protected function hasViewAction(): bool
    {
        return false;
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    protected function getFooterWidgets(): array
    {
        return [];
    }

    public function getTreeRecordIcon(?Model $record = null): ?string
    {
        if (! $record) {
            return null;
        }

        return match ($record->type) {
            MenuItemType::Linkable => 'heroicon-o-link',
            MenuItemType::CustomUrl => 'heroicon-o-globe-alt',
            MenuItemType::Group => 'heroicon-o-folder',
        };
    }

    protected function getFormSchema(): array
    {
        return MenuItemResource::getMenuItemFormSchema();
    }
}
