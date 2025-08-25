<?php

namespace Eclipse\Cms\Models;

use Eclipse\Cms\Factories\MenuFactory;
use Eclipse\Cms\Models\Menu\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Menu extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $table = 'cms_menus';

    protected static function newFactory(): MenuFactory
    {
        return MenuFactory::new();
    }

    protected static function boot(): void
    {
        parent::boot();

        if (config('eclipse-cms.tenancy.enabled')) {
            static::addGlobalScope('tenant', function (Builder $builder) {
                $tenantModel = config('eclipse-cms.tenancy.model');
                if ($tenantModel && class_exists($tenantModel)) {
                    $currentSite = $tenantModel::first();
                    if ($currentSite) {
                        $tenantFK = config('eclipse-cms.tenancy.foreign_key', 'site_id');
                        $builder->where($tenantFK, $currentSite->id);
                    }
                }
            });
        }
    }

    public function getFillable(): array
    {
        $attr = [
            'title',
            'is_active',
            'code',
        ];

        if (config('eclipse-cms.tenancy.enabled')) {
            $attr[] = config('eclipse-cms.tenancy.foreign_key');
        }

        return $attr;
    }

    public array $translatable = [
        'title',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class)->where('parent_id', -1)->orderBy('sort');
    }

    public function allItems(): HasMany
    {
        return $this->hasMany(Item::class)->orderedForTree();
    }

    public function site(): BelongsTo
    {
        $tenantModel = config('eclipse-cms.tenancy.model');
        $tenantFK = config('eclipse-cms.tenancy.foreign_key', 'site_id');

        return $this->belongsTo($tenantModel, $tenantFK);
    }
}
