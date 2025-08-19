<?php

namespace Eclipse\Cms\Models\Menu;

use Eclipse\Cms\Enums\MenuItemType;
use Eclipse\Cms\Factories\MenuItemFactory;
use Eclipse\Cms\Models\Menu;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use SolutionForest\FilamentTree\Concern\ModelTree;
use Spatie\Translatable\HasTranslations;

class Item extends Model
{
    use HasFactory, HasTranslations, ModelTree, SoftDeletes;

    protected $table = 'cms_menu_items';

    protected static function newFactory(): MenuItemFactory
    {
        return MenuItemFactory::new();
    }

    protected $fillable = [
        'label',
        'menu_id',
        'parent_id',
        'type',
        'linkable_class',
        'linkable_id',
        'custom_url',
        'new_tab',
        'is_active',
        'sort',
    ];

    public array $translatable = [
        'label',
    ];

    protected function casts(): array
    {
        return [
            'label' => 'array',
            'type' => MenuItemType::class,
            'new_tab' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Item::class, 'parent_id')->orderBy('sort');
    }

    public function linkable(): MorphTo
    {
        return $this->morphTo('linkable', 'linkable_class', 'linkable_id');
    }

    public function determineStatusUsing(): string
    {
        return 'is_active';
    }

    public function determineTitleColumnName(): string
    {
        return 'label';
    }

    public function determineOrderColumnName(): string
    {
        return 'sort';
    }

    public function getUrl(): ?string
    {
        return match ($this->type) {
            MenuItemType::Linkable => $this->linkable?->getUrl(),
            MenuItemType::CustomUrl => $this->custom_url,
            MenuItemType::Group => null,
        };
    }

    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRootItems($query)
    {
        return $query->where('parent_id', -1);
    }

    protected static function formatTreeName(string $value): array
    {
        if (! str_starts_with($value, '-')) {
            return ['name' => $value, 'level' => 0];
        }

        $dashCount = 0;
        while ($dashCount < strlen($value) && $value[$dashCount] === '-') {
            $dashCount++;
        }

        $level = intval($dashCount / 3);
        $cleanName = ltrim($value, '-');

        return ['name' => $cleanName, 'level' => $level];
    }

    protected static function getTreePrefix(int $level): string
    {
        $indent = str_repeat('. . . . ', $level);
        $connector = $level > 0 ? '└─ ' : '';

        return $indent.$connector;
    }

    public function getTreeFormattedName(): string
    {
        $selectArray = static::selectArray();
        $formattedName = $selectArray[$this->id] ?? $this->label;

        $formatted = self::formatTreeName($formattedName);

        return self::getTreePrefix($formatted['level']).e($formatted['name']);
    }

    public function getFullPath(): string
    {
        $allNodes = static::allNodes()->keyBy('id');

        $path = [];
        $current = $this;

        while ($current) {
            $path[] = $current->title ?? $current->label;
            $parentId = $current->{$this->determineParentColumnName()};

            if ($parentId && $parentId !== static::defaultParentKey() && isset($allNodes[$parentId])) {
                $current = $allNodes[$parentId];
            } else {
                $current = null;
            }
        }

        return implode(' > ', array_reverse($path));
    }

    public static function getHierarchicalOptions(?int $menuId = null): array
    {
        $query = static::query();

        if ($menuId) {
            $query->where('menu_id', $menuId);
        }

        $options = $query->pluck('label', 'id')->toArray();
        $selectArray = static::selectArray();

        foreach ($options as $key => $value) {
            if (isset($selectArray[$key])) {
                $formatted = self::formatTreeName($selectArray[$key]);
                $options[$key] = self::getTreePrefix($formatted['level']).$formatted['name'];
            }
        }

        return $options;
    }
}
