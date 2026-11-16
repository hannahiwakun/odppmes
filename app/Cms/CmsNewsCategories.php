<?php

namespace App\Cms;

final class CmsNewsCategories
{
    public const DEFAULT = 'Announcements';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            'Government News',
            'Announcements',
            'Projects and Development',
            'Events',
            'Policies and Programmes',
            'Press Releases',
        ];
    }

    public static function isValid(?string $category): bool
    {
        return in_array((string) $category, self::all(), true);
    }

    public static function display(?string $category): string
    {
        if (self::isValid($category)) {
            return (string) $category;
        }

        return self::DEFAULT;
    }
}
