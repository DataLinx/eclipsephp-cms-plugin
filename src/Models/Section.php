<?php

namespace Eclipse\Cms\Models;

use Eclipse\Cms\Enums\SectionType;
use Eclipse\Cms\Factories\SectionFactory;
use Eclipse\Common\Foundation\Models\IsSearchable;
use Eclipse\Core\Models\Site;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Section extends Model
{
    use HasFactory, HasTranslations, IsSearchable, SoftDeletes;

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

    public function site(): BelongsTo
    {
        $siteModel = config('eclipse-cms.tenancy.model', Site::class);

        return $this->belongsTo($siteModel);
    }

    protected static function booted()
    {
        if (config('eclipse-cms.tenancy.enabled') && app()->bound('filament')) {
            static::addGlobalScope('site', function (Builder $builder) {
                if ($tenant = filament()->getTenant()) {
                    $builder->where(config('eclipse-cms.tenancy.foreign_key'), $tenant->getKey());
                }
            });

            static::creating(function (Section $section) {
                if ($tenant = filament()->getTenant()) {
                    $section->{config('eclipse-cms.tenancy.foreign_key')} = $tenant->getKey();
                }
            });
        }
    }

    protected static function newFactory(): SectionFactory
    {
        return SectionFactory::new();
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->getTranslations('name'),
            'type' => $this->type->value,
            'site_id' => $this->site_id,
        ];
    }
}
