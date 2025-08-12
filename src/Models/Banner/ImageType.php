<?php

namespace Eclipse\Cms\Models\Banner;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Type of banner image
 *
 * @property int $id Type ID
 * @property int $position_id Position ID
 * @property string $name Type name (e.g. Desktop, Mobile)
 * @property string|null $code Optional type code (for programmatic use)
 * @property int|null $image_width Optional image width (for upload validation)
 * @property int|null $image_height Optional image height (for upload validation)
 * @property bool $is_hidpi Whether to require hidpi images (x2) when uploading
 */
class ImageType extends Model
{
    public $timestamps = false;

    protected $table = 'cms_banner_image_types';

    protected $fillable = [
        'name',
        'position_id',
        'code',
        'image_width',
        'image_height',
        'is_hidpi',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    protected function casts(): array
    {
        return [
            'is_hidpi' => 'boolean',
        ];
    }
}
