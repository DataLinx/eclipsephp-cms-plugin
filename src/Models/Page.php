<?php

namespace Eclipse\Cms\Models;

use Eclipse\Cms\Factories\PageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Page extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $table = 'cms_pages';

    public array $translatable = [
        'title',
    ];

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

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function getUrl(): ?string
    {
        return $this->sef_key ? "/{$this->sef_key}" : null;
    }

    protected static function newFactory(): PageFactory
    {
        return PageFactory::new();
    }
}
