<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Workbench\Database\Factories\SiteFactory;

class Site extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    protected static function newFactory()
    {
        return SiteFactory::new();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(\Eclipse\Cms\Models\Section::class, 'site_id');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(\Eclipse\Cms\Models\Page::class, 'site_id');
    }
}
