<?php

namespace Eclipse\Cms\Models;

use Eclipse\Cms\Enums\SectionType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Eclipse\Cms\Factories\SectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Section extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cms_sections';

    protected $casts = [
        'type' => SectionType::class,
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
