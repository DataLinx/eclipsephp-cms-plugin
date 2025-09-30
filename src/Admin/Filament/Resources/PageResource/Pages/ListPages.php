<?php

namespace Eclipse\Cms\Admin\Filament\Resources\PageResource\Pages;

use Eclipse\Cms\Admin\Filament\Resources\PageResource;
use Eclipse\Cms\Models\Section;
use Eclipse\Common\Foundation\Pages\HasScoutSearch;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPages extends ListRecords
{
    use HasScoutSearch;

    protected static string $resource = PageResource::class;

    public function getTitle(): string
    {
        if ($sectionId = request()->get('sId')) {
            $section = Section::find($sectionId);
            if ($section) {
                return $section->name;
            }
        }

        return parent::getTitle();
    }

    public function getBreadcrumb(): ?string
    {
        if ($sectionId = request()->get('sId')) {
            $section = Section::find($sectionId);
            if ($section) {
                return $section->name;
            }
        }

        return parent::getBreadcrumb();
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        if ($sectionId = request()->get('sId')) {
            $query->where('section_id', $sectionId);
        }

        return $query;
    }

    protected function getHeaderActions(): array
    {
        $createAction = CreateAction::make();

        if (request()->get('sId')) {
            $createAction
                ->url(fn () => PageResource::getUrl('create', [
                    'sId' => request()->get('sId'),
                ]));
        }

        return [
            $createAction,
        ];
    }
}
