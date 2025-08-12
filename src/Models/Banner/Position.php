<?php

namespace Eclipse\Cms\Models\Banner;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id Position ID
 * @property string $name Position name
 * @property string|null code Optional position code (for programmatic use)
 */
class Position extends Model
{
    use SoftDeletes;

    protected $table = 'cms_banner_positions';

    protected $fillable = [
        'name',
        'code',
    ];
}
