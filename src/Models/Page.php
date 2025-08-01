<?php

namespace Eclipse\Cms\Models;

use Eclipse\Cms\Enums\PageStatus;
use Eclipse\Cms\Factories\PageFactory;
use Eclipse\Common\Foundation\Models\IsSearchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Translatable\HasTranslations;

class Page extends Model
{
    use HasFactory, HasTranslations, IsSearchable, SoftDeletes;

    protected $table = 'cms_pages';

    public $translatable = ['title', 'short_text', 'long_text', 'sef_key'];

    protected $fillable = [
        'title',
        'section_id',
        'short_text',
        'long_text',
        'sef_key',
        'code',
        'status',
        'type',
    ];

    protected $casts = [
        'status' => PageStatus::class,
        'title' => 'array',
        'short_text' => 'array',
        'long_text' => 'array',
        'sef_key' => 'array',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function site()
    {
        return $this->hasOneThrough(
            config('eclipse-cms.tenancy.model'),
            Section::class,
            'id',
            'id',
            'section_id',
            'site_id'
        );
    }

    protected static function booted()
    {
        static::creating(function (Page $page) {
            if (! $page->relationLoaded('section')) {
                $page->load('section');
            }

            if ($page->section && ! $page->type) {
                $page->type = $page->section->type->name;
            }

            if (! $page->sef_key && $page->title) {
                $page->sef_key = Str::slug($page->title);
            }

            static::validateUniqueSefKey($page);
        });

        static::updating(function (Page $page) {
            if (! $page->sef_key && $page->title) {
                $page->sef_key = Str::slug($page->title);
            }

            static::validateUniqueSefKey($page);
        });

        if (config('eclipse-cms.tenancy.enabled') && app()->bound('filament')) {
            static::addGlobalScope('tenant_sections', function (Builder $builder) {
                if ($tenant = filament()->getTenant()) {
                    $builder->whereHas('section', function (Builder $query) use ($tenant) {
                        $query->where(config('eclipse-cms.tenancy.foreign_key'), $tenant->getKey());
                    });
                }
            });
        }
    }

    protected static function validateUniqueSefKey(Page $page): void
    {
        $sefKeyForComparison = is_string($page->sef_key)
            ? json_encode([app()->getLocale() => $page->sef_key])
            : json_encode($page->sef_key);

        if (! $page->relationLoaded('section')) {
            $page->load('section');
        }

        if (! $page->section) {
            return;
        }

        $query = static::query()
            ->where('sef_key', $sefKeyForComparison)
            ->whereHas('section', function (Builder $query) use ($page) {
                $query->where(config('eclipse-cms.tenancy.foreign_key'), $page->section->site_id);
            });

        if ($page->exists) {
            $query->whereNot('id', $page->id);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'sef_key' => 'The SEF key must be unique within the site.',
            ]);
        }
    }

    protected static function newFactory(): PageFactory
    {
        return PageFactory::new();
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->getTranslations('title'),
            'short_text' => $this->getTranslations('short_text'),
            'long_text' => $this->getTranslations('long_text'),
            'sef_key' => $this->getTranslations('sef_key'),
            'status' => $this->status->value,
            'type' => $this->type,
            'section_id' => $this->section_id,
            'section_name' => $this->section?->getTranslations('name'),
            'site_id' => $this->section?->site_id,
        ];
    }
}
