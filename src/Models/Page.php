<?php

namespace Eclipse\Cms\Models;

use Eclipse\Cms\Factories\PageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cms_pages';

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

    protected static function newFactory(): PageFactory
    {
        return PageFactory::new();
    }
}
