<?php

namespace Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\RelationManagers;

use Eclipse\Cms\Models\Banner;
use Eclipse\Cms\Rules\BannerImageDimensionRule;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\Concerns\Translatable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BannerRelationManager extends RelationManager
{
    use Translatable;

    protected static string $relationship = 'banners';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->compact()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('link')
                            ->url()
                            ->maxLength(255),

                        Forms\Components\Toggle::make('is_active')
                            ->default(true),

                        Forms\Components\Toggle::make('new_tab')
                            ->default(false)
                            ->label('Open in new tab'),
                    ]),

                Forms\Components\Repeater::make('images')
                    ->relationship()
                    ->columnSpanFull()
                    ->hiddenLabel()
                    ->schema([
                        Forms\Components\Hidden::make('type_id'),
                        Forms\Components\Hidden::make('is_hidpi'),
                        Forms\Components\Hidden::make('image_width'),
                        Forms\Components\Hidden::make('image_height'),
                        FileUpload::make('file')
                            ->hiddenLabel()
                            ->image()
                            ->directory('banners')
                            ->rules([
                                function (Get $get): BannerImageDimensionRule|string {
                                    $typeId = $get('type_id');
                                    $isHidpi = $get('is_hidpi') ?? false;

                                    if ($typeId) {
                                        return new BannerImageDimensionRule(
                                            $this->getOwnerRecord(),
                                            $typeId,
                                            $isHidpi
                                        );
                                    }

                                    return 'nullable';
                                },
                            ])
                            ->helperText(function (Get $get): string {
                                $typeId = $get('type_id');
                                $isHidpi = $get('is_hidpi');

                                if ($typeId) {
                                    $imageType = $this->getOwnerRecord()->imageTypes()->find($typeId);
                                    if ($imageType && $imageType->image_width && $imageType->image_height) {
                                        if ($isHidpi) {
                                            $hidpiWidth = $imageType->image_width * 2;
                                            $hidpiHeight = $imageType->image_height * 2;

                                            return "{$imageType->name} @2x ({$hidpiWidth}×{$hidpiHeight}, displayed as {$imageType->image_width}×{$imageType->image_height})";
                                        } else {
                                            return "{$imageType->name} ({$imageType->image_width}×{$imageType->image_height})";
                                        }
                                    }
                                }

                                return 'Upload banner image';
                            }),
                    ])
                    ->default(function () {
                        $position = $this->getOwnerRecord();
                        if (! $position) {
                            return [];
                        }

                        $items = [];
                        $position->imageTypes()->get()->each(function ($imageType) use (&$items) {
                            if ($imageType->is_hidpi) {
                                $items[] = [
                                    'type_id' => $imageType->id,
                                    'is_hidpi' => true,
                                    'image_width' => $imageType->image_width,
                                    'image_height' => $imageType->image_height,
                                ];
                            } else {
                                $items[] = [
                                    'type_id' => $imageType->id,
                                    'is_hidpi' => false,
                                    'image_width' => $imageType->image_width,
                                    'image_height' => $imageType->image_height,
                                ];
                            }
                        });

                        return $items;
                    })
                    ->minItems(0)
                    ->maxItems(function (): int {
                        $position = $this->getOwnerRecord();
                        if (! $position) {
                            return 0;
                        }

                        return $position->imageTypes()->count();
                    })
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false)
                    ->itemLabel(function (array $state): string {
                        $typeId = $state['type_id'] ?? null;
                        $isHidpi = $state['is_hidpi'] ?? false;

                        if ($typeId) {
                            $imageType = $this->getOwnerRecord()->imageTypes()->find($typeId);
                            if ($imageType) {
                                if ($imageType->image_width && $imageType->image_height) {
                                    if ($isHidpi) {
                                        $hidpiWidth = $imageType->image_width * 2;
                                        $hidpiHeight = $imageType->image_height * 2;

                                        return "{$imageType->name} @2x ({$hidpiWidth}×{$hidpiHeight}, displayed as {$imageType->image_width}×{$imageType->image_height})";
                                    } else {
                                        return "{$imageType->name} ({$imageType->image_width}×{$imageType->image_height})";
                                    }
                                }

                                return $imageType->name;
                            }
                        }

                        return 'Banner Image';
                    }),
            ]);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('name'),

                Infolists\Components\TextEntry::make('link')
                    ->url(fn ($record) => $record->link)
                    ->openUrlInNewTab(fn ($record) => $record->new_tab),

                Infolists\Components\IconEntry::make('is_active')
                    ->boolean(),

                Infolists\Components\IconEntry::make('new_tab')
                    ->boolean()
                    ->label('Open in new tab'),

                Infolists\Components\Grid::make()
                    ->columnSpanFull()
                    ->schema(
                        fn (Banner $record) => $record->images->load('type')->map(
                            fn ($image) => Infolists\Components\ImageEntry::make("image_{$image->id}")
                                ->columnSpanFull()
                                ->label($image->type->name ?? 'Image')
                                ->width('100%')
                                ->height('auto')
                                ->getStateUsing(fn () => $image->getTranslation('file', app()->getLocale()))
                        )->toArray()
                    ),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('sort')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\ImageColumn::make('images.file')
                    ->circular()
                    ->stacked()
                    ->getStateUsing(function (Banner $record) {
                        $locale = $this->activeLocale ?? app()->getLocale();

                        return $record->images->map(function ($image) use ($locale) {
                            return $image->getTranslation('file', $locale);
                        })->filter()->values()->toArray();
                    })
                    ->preview(function (Model $record) {
                        $locale = $this->activeLocale ?? app()->getLocale();

                        return [
                            'title' => $record->getTranslation('name', $locale).' Banner',
                            'link' => $record->link ?? '#',
                        ];
                    }),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('link')
                    ->limit(30)
                    ->url(fn ($record) => $record->link)
                    ->openUrlInNewTab(fn ($record) => $record->new_tab),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('new_tab')
                    ->boolean()
                    ->icon(fn ($state): string => match ($state) {
                        true => 'heroicon-o-arrow-top-right-on-square',
                        false => 'heroicon-o-minus',
                    })
                    ->label('New Tab'),
            ])
            ->defaultSort('sort')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->boolean()
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only')
                    ->native(false),

                Tables\Filters\TernaryFilter::make('new_tab')
                    ->label('Open in New Tab')
                    ->boolean()
                    ->trueLabel('New tab only')
                    ->falseLabel('Same tab only')
                    ->native(false),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->reorderable('sort')
            ->headerActions([
                Tables\Actions\LocaleSwitcher::make(),
                Tables\Actions\CreateAction::make()
                    ->using(function (array $data, string $model): Banner {
                        $position = $this->getOwnerRecord();

                        $data['position_id'] = $position->id;
                        $maxSort = $position->banners()->max('sort') ?? 0;
                        $data['sort'] = $maxSort + 1;

                        $imagesData = $data['images'] ?? [];
                        unset($data['images']);

                        $banner = $position->banners()->create($data);

                        foreach ($imagesData as $imageData) {
                            if (isset($imageData['type_id']) && ! empty($imageData['file'])) {
                                $imageType = $position->imageTypes()->find($imageData['type_id']);
                                if ($imageType) {
                                    $banner->images()->create([
                                        'type_id' => $imageData['type_id'],
                                        'is_hidpi' => $imageData['is_hidpi'] ?? false,
                                        'file' => $imageData['file'],
                                        'image_width' => $imageType->image_width,
                                        'image_height' => $imageType->image_height,
                                    ]);
                                }
                            }
                        }

                        $banner->refresh();
                        $banner->touch();

                        return $banner;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->using(function (Banner $record, array $data): Banner {
                        $position = $this->getOwnerRecord();

                        $imagesData = $data['images'] ?? [];
                        unset($data['images']);

                        $record->update($data);

                        $record->images()->delete();

                        foreach ($imagesData as $imageData) {
                            if (isset($imageData['type_id']) && ! empty($imageData['file'])) {
                                $imageType = $position->imageTypes()->find($imageData['type_id']);
                                if ($imageType) {
                                    $record->images()->create([
                                        'type_id' => $imageData['type_id'],
                                        'is_hidpi' => $imageData['is_hidpi'] ?? false,
                                        'file' => $imageData['file'],
                                        'image_width' => $imageType->image_width,
                                        'image_height' => $imageType->image_height,
                                    ]);
                                }
                            }
                        }

                        $record->refresh();
                        $record->touch();

                        return $record;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}
