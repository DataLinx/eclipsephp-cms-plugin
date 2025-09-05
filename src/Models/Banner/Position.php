<?php

namespace Eclipse\Cms\Models\Banner;

use Eclipse\Cms\Factories\BannerPositionFactory;
use Eclipse\Cms\Models\Banner;
use Eclipse\Cms\Observers\PositionObserver;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

#[ObservedBy([PositionObserver::class])]
class Position extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $table = 'cms_banner_positions';

    protected static function boot(): void
    {
        parent::boot();

        if (config('eclipse-cms.tenancy.enabled')) {
            static::addGlobalScope('tenant', function (Builder $builder) {
                $tenant = Filament::getTenant();
                if ($tenant) {
                    $tenantFK = config('eclipse-cms.tenancy.foreign_key', 'site_id');
                    $builder->where($tenantFK, $tenant->id);
                }
            });

            static::creating(function (Position $model) {
                $tenant = Filament::getTenant();
                if ($tenant) {
                    $tenantFK = config('eclipse-cms.tenancy.foreign_key', 'site_id');
                    $model->setAttribute($tenantFK, $tenant->id);
                }
            });
        }
    }

    public function getFillable(): array
    {
        $attr = [
            'name',
            'code',
        ];

        if (config('eclipse-cms.tenancy.enabled')) {
            $attr[] = config('eclipse-cms.tenancy.foreign_key');
        }

        return $attr;
    }

    public array $translatable = [
        'name',
    ];

    public function imageTypes(): HasMany
    {
        return $this->hasMany(ImageType::class, 'position_id');
    }

    public function banners(): HasMany
    {
        return $this->hasMany(Banner::class, 'position_id')->orderBy('sort');
    }

    protected static function newFactory()
    {
        return BannerPositionFactory::new();
    }

    public function site(): BelongsTo
    {
        $tenantModel = config('eclipse-cms.tenancy.model');
        $tenantFK = config('eclipse-cms.tenancy.foreign_key', 'site_id');

        return $this->belongsTo($tenantModel, $tenantFK);
    }
}
