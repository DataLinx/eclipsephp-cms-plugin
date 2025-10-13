<?php

namespace Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\RelationManagers;

use Eclipse\Cms\Models\Banner;
use Eclipse\Cms\Rules\BannerImageDimensionRule;
use Eclipse\Common\Filament\Tables\Columns\ImageColumn;
use Eclipse\Common\Helpers\MediaHelper;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\RelationManagers\Concerns\Translatable;

class BannerRelationManager extends RelationManager
{
    use Translatable;

    protected static string $relationship = 'banners';

    protected static ?string $recordTitleAttribute = 'name';

    public function isReadOnly(): bool
    {
        return false;
    }

    protected function getDynamicImageColumns(): array
    {
        $position = $this->getOwnerRecord();
        if (! $position) {
            return [];
        }

        $imageTypes = $position->imageTypes()->get();

        return $imageTypes->map(function ($imageType) {
            return ImageColumn::make("image_type_{$imageType->id}")
                ->label($imageType->name)
                ->preview()
                ->getStateUsing(function (Banner $record) use ($imageType) {
                    $locale = $this->activeLocale ?? app()->getLocale();
                    $image = $record->images->where('type_id', $imageType->id)->first();

                    if ($image?->getTranslation('file', $locale)) {
                        return $image->getTranslation('file', $locale);
                    }

                    if (class_exists(MediaHelper::class)) {
                        return MediaHelper::getPlaceholderImageUrl(
                            'Not Found',
                            $imageType->image_width ?? 120,
                            $imageType->image_height ?? 120
                        );
                    }

                    return null;
                })
                ->title(function (Banner $record) use ($imageType) {
                    $locale = $this->activeLocale ?? app()->getLocale();

                    return $record->getTranslation('name', $locale).' - '.$imageType->name;
                })
                ->link(fn (Banner $record) => $record->link ?? '#')
                ->sortable(false);
        })->toArray();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->columnSpanFull()
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
                        Forms\Components\FileUpload::make('file')
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

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Infolists\Components\TextEntry::make('name'),

                Infolists\Components\TextEntry::make('link')
                    ->url(fn ($record) => $record->link)
                    ->openUrlInNewTab(fn ($record) => $record->new_tab),

                Infolists\Components\IconEntry::make('is_active')
                    ->boolean(),

                Infolists\Components\IconEntry::make('new_tab')
                    ->boolean()
                    ->label('Open in new tab'),

                Grid::make()
                    ->columnSpanFull()
                    ->schema(
                        fn (Banner $record) => $this->getOwnerRecord()->imageTypes()->get()
                            ->map(function ($imageType) use ($record) {
                                $locale = app()->getLocale();
                                $image = $record->images->where('type_id', $imageType->id)->first();

                                if (! $image || ! $image->getTranslation('file', $locale)) {
                                    return null;
                                }

                                return Infolists\Components\ImageEntry::make("image_type_{$imageType->id}")
                                    ->columnSpanFull()
                                    ->label($imageType->name ?? 'Image')
                                    ->width('100%')
                                    ->height('auto')
                                    ->getStateUsing(fn () => $image->getTranslation('file', $locale));
                            })
                            ->filter()
                            ->toArray()
                    ),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns(array_merge([
                Tables\Columns\TextColumn::make('sort')
                    ->label('Position')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
            ], $this->getDynamicImageColumns(), [
                Tables\Columns\TextColumn::make('link')
                    ->limit(30)
                    ->url(fn ($record) => $record->link)
                    ->openUrlInNewTab(),

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
            ]))
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
                LocaleSwitcher::make(),
                Actions\CreateAction::make()
                    ->icon('heroicon-o-plus-circle')
                    ->label('Add banner')
                    ->mutateFormDataUsing(function (array $data): array {
                        $position = $this->getOwnerRecord();
                        $data['position_id'] = $position->id;
                        $data['sort'] = ($position->banners()->max('sort') ?? 0) + 1;

                        return $data;
                    }),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(
                fn (Builder $query) => $query
                    ->with(['images', 'images.type'])
                    ->withoutGlobalScopes([
                        SoftDeletingScope::class,
                    ])
            );
    }
}
