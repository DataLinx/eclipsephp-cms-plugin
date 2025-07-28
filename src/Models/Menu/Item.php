<?php

namespace Eclipse\Cms\Models\Menu;

use Eclipse\Cms\Models\Menu;
use Eclipse\Cms\Enums\MenuItemType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;

    protected $table = 'cms_menu_items';

    protected $fillable = [
        'label',
        'menu_id',
        'type',
        'new_tab',
        'is_active',
        'sort',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    protected function casts(): array
    {
        return [
            'type' => MenuItemType::class,
            'new_tab' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
