<?php

namespace Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\RelationManagers;

use Closure;
use Eclipse\Cms\Models\Banner;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\Concerns\Translatable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\LocaleSwitcher;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Table;

class BannersRelationManager extends RelationManager
{
    use Translatable;

    protected static string $relationship = 'banners';

    public function form(Form $form): Form
    {
        return $form
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

                Forms\Components\Repeater::make('images')
                    ->relationship()
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Hidden::make('type_id'),
                        Forms\Components\Hidden::make('is_hidpi'),
                        FileUpload::make('file')
                            ->hiddenLabel()
                            ->image()
                            ->directory('banners')
                            ->required()
                            ->rules([
                                function (Get $get) {
                                    return function (string $attribute, $value, Closure $fail) use ($get): void {
                                        if (! $value) {
                                            return;
                                        }

                                        $typeId = $get('type_id');
                                        $isHidpi = $get('is_hidpi');

                                        if ($typeId) {
                                            $imageType = $this->getOwnerRecord()->imageTypes()->find($typeId);
                                            if ($imageType && $imageType->image_width && $imageType->image_height) {
                                                $expectedWidth = $isHidpi ? $imageType->image_width * 2 : $imageType->image_width;
                                                $expectedHeight = $isHidpi ? $imageType->image_height * 2 : $imageType->image_height;

                                                $imageSize = getimagesize($value->getPathname());
                                                $actualWidth = $imageSize[0] ?? 0;
                                                $actualHeight = $imageSize[1] ?? 0;

                                                if ($actualWidth !== $expectedWidth || $actualHeight !== $expectedHeight) {
                                                    $fail("Image must be exactly {$expectedWidth}×{$expectedHeight}px. Got {$actualWidth}×{$actualHeight}px.");
                                                }
                                            }
                                        }
                                    };
                                },
                            ])
                            ->helperText(function (Get $get): string {
                                $typeId = $get('type_id');
                                $isHidpi = $get('is_hidpi');

                                if ($typeId) {
                                    $imageType = $this->getOwnerRecord()->imageTypes()->find($typeId);
                                    if ($imageType && $imageType->image_width && $imageType->image_height) {
                                        if ($isHidpi) {
                                            $regularWidth = $imageType->image_width;
                                            $regularHeight = $imageType->image_height;
                                            $hidpiWidth = $regularWidth * 2;
                                            $hidpiHeight = $regularHeight * 2;

                                            return "Expected HiDPI size: {$hidpiWidth}px × {$hidpiHeight}px (2x of {$regularWidth}×{$regularHeight})";
                                        } else {
                                            return "Expected size: {$imageType->image_width}px × {$imageType->image_height}px";
                                        }
                                    }
                                }

                                return 'Upload banner image';
                            }),
                    ])
                    ->default(function () {
                        $items = [];
                        $this->getOwnerRecord()->imageTypes()->get()->each(function ($imageType) use (&$items) {
                            $items[] = ['type_id' => $imageType->id, 'is_hidpi' => false];
                            if ($imageType->is_hidpi) {
                                $items[] = ['type_id' => $imageType->id, 'is_hidpi' => true];
                            }
                        });

                        return $items;
                    })
                    ->minItems(function (): int {
                        $count = 0;
                        $this->getOwnerRecord()->imageTypes()->get()->each(function ($imageType) use (&$count) {
                            $count++;
                            if ($imageType->is_hidpi) {
                                $count++;
                            }
                        });

                        return $count;
                    })
                    ->maxItems(function (): int {
                        $count = 0;
                        $this->getOwnerRecord()->imageTypes()->get()->each(function ($imageType) use (&$count) {
                            $count++;
                            if ($imageType->is_hidpi) {
                                $count++;
                            }
                        });

                        return $count;
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
                                $dimensions = '';
                                if ($imageType->image_width && $imageType->image_height) {
                                    if ($isHidpi) {
                                        $dimensions = " (@2x: {$imageType->image_width}×{$imageType->image_height} → ".
                                                     ($imageType->image_width * 2).'×'.($imageType->image_height * 2).')';
                                    } else {
                                        $dimensions = " ({$imageType->image_width}×{$imageType->image_height})";
                                    }
                                }

                                return $imageType->name.($isHidpi ? ' @2x' : '').$dimensions;
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
                                ->getStateUsing(fn () => $image->getTranslation('file', $this->activeLocale ?? app()->getLocale()))
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
                    ->preview(true),

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
            ->reorderable('sort')
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
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $maxSort = $this->getOwnerRecord()->banners()->max('sort') ?? 0;
                        $data['sort'] = $maxSort + 1;

                        return $data;
                    }),
                LocaleSwitcher::make(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
