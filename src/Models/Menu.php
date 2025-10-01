<?php

namespace Eclipse\Cms\Models;

use Eclipse\Cms\Factories\MenuFactory;
use Eclipse\Cms\Models\Menu\Item;
use Eclipse\Common\Foundation\Models\Scopes\ActiveScope;
use Filament\Facades\Filament;
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

        static::deleting(function ($menu) {
            $menu->allItems()->get()->each(function ($item) {
                $item->delete();
            });
        });

        static::forceDeleting(function ($menu) {
            $menu->allItems()->withTrashed()->get()->each(function ($item) {
                $item->forceDelete();
            });
        });

        if (config('eclipse-cms.tenancy.enabled')) {
            static::addGlobalScope('tenant', function (Builder $builder) {
                $currentTenant = Filament::getTenant();
                if ($currentTenant) {
                    $tenantFK = config('eclipse-cms.tenancy.foreign_key', 'site_id');
                    $builder->where($tenantFK, $currentTenant->getKey());
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
        return $this->hasMany(Item::class)
            ->withoutGlobalScope(ActiveScope::class)
            ->orderedForTree();
    }

    public function site(): BelongsTo
    {
        $tenantModel = config('eclipse-cms.tenancy.model');
        $tenantFK = config('eclipse-cms.tenancy.foreign_key', 'site_id');

        return $this->belongsTo($tenantModel, $tenantFK);
    }
}
