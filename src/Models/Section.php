<?php

namespace Eclipse\Cms\Models;

use Eclipse\Cms\Enums\SectionType;
use Eclipse\Cms\Factories\SectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Section extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $table = 'cms_sections';

    public array $translatable = [
        'name',
    ];

    protected function casts(): array
    {
        return [
            'type' => SectionType::class,
        ];
    }

    public function getFillable()
    {
        $attr = [
            'name',
            'type',
        ];

        if (config('eclipse-cms.tenancy.enabled')) {
            $attr[] = config('eclipse-cms.tenancy.foreign_key');
        }

        return $attr;
    }

    public function getUrl(): ?string
    {
        return "/section/{$this->id}";
    }

    protected static function newFactory(): SectionFactory
    {
        return SectionFactory::new();
    }
}
