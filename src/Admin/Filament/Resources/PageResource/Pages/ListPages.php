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

    public ?int $sectionId = null;

    public function mount(): void
    {
        parent::mount();

        $this->sectionId = request()->get('sId');
    }

    public function getTitle(): string
    {
        if ($this->sectionId) {
            $section = Section::find($this->sectionId);
            if ($section) {
                return $section->name;
            }
        }

        return parent::getTitle();
    }

    public function getBreadcrumb(): ?string
    {
        if ($this->sectionId) {
            $section = Section::find($this->sectionId);
            if ($section) {
                return $section->name;
            }
        }

        return parent::getBreadcrumb();
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        if ($this->sectionId) {
            $query->where('section_id', $this->sectionId);
        }

        return $query;
    }

    protected function getHeaderActions(): array
    {
        $createAction = CreateAction::make();

        if ($this->sectionId) {
            $createAction
                ->url(fn () => PageResource::getUrl('create', [
                    'sId' => $this->sectionId,
                ]));
        }

        return [
            $createAction,
        ];
    }
}
