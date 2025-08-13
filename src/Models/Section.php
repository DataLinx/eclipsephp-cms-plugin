<?php

namespace Eclipse\Cms\Models;

use Eclipse\Cms\Enums\SectionType;
use Eclipse\Cms\Factories\SectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Section extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $table = 'cms_sections';

    public $translatable = ['name'];

    protected $casts = [
        'type' => SectionType::class,
        'name' => 'array',
    ];

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

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    protected static function newFactory(): SectionFactory
    {
        return SectionFactory::new();
    }

    /** @return BelongsTo<\Eclipse\Core\Models\Site, self> */
    public function site(): BelongsTo
    {
        return $this->belongsTo(\Eclipse\Core\Models\Site::class);
    }
}
