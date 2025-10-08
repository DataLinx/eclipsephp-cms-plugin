<?php

namespace Eclipse\Cms\Models;

use Eclipse\Cms\Factories\BannerFactory;
use Eclipse\Cms\Models\Banner\Image;
use Eclipse\Cms\Models\Banner\Position;
use Eclipse\Cms\Observers\BannerObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

#[ObservedBy([BannerObserver::class])]
class Banner extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $table = 'cms_banners';

    protected $fillable = [
        'position_id',
        'name',
        'link',
        'is_active',
        'new_tab',
        'sort',
    ];

    public array $translatable = [
        'name',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class, 'banner_id');
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'new_tab' => 'boolean',
        ];
    }

    protected static function newFactory(): BannerFactory
    {
        return BannerFactory::new();
    }
}
