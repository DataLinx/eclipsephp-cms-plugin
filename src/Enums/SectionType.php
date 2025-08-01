<?php

namespace Eclipse\Cms\Enums;

use Filament\Support\Contracts\HasLabel;

enum SectionType: string implements HasLabel
{
    case Pages = 'pages';
    case News = 'news';
    case Products = 'products';
    case Gallery = 'gallery';
    case About = 'about';
    case Services = 'services';
    case Blog = 'blog';
    case Events = 'events';
    case Testimonials = 'testimonials';
    case FAQ = 'faq';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pages => 'Pages',
            self::News => 'News',
            self::Products => 'Products',
            self::Gallery => 'Gallery',
            self::About => 'About',
            self::Services => 'Services',
            self::Blog => 'Blog',
            self::Events => 'Events',
            self::Testimonials => 'Testimonials',
            self::FAQ => 'FAQ',
        };
    }
}
