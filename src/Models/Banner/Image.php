<?php

namespace Eclipse\Cms\Models\Banner;

use Eclipse\Cms\Models\Banner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id Image ID
 * @property int $banner_id Banner ID
 * @property int $type_id Banner image type ID
 * @property string $file Image file path
 * @property bool $is_hidpi Whether the image is hidpi (x2)
 * @property int $image_width Image width
 * @property int $image_height Image height
 */
class Image extends Model
{
    public $timestamps = false;

    protected $table = 'cms_banner_images';

    protected $fillable = [
        'banner_id',
        'type_id',
        'file',
        'is_hidpi',
        'image_width',
        'image_height',
    ];

    public function banner(): BelongsTo
    {
        return $this->belongsTo(Banner::class, 'banner_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ImageType::class, 'type_id');
    }

    protected function casts(): array
    {
        return [
            'is_hidpi' => 'boolean',
        ];
    }
}
