<?php

namespace App\Cms;

use App\Models\CmsHomepageBanner;

final class CmsHomepageBannerTypes
{
    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            CmsHomepageBanner::TYPE_HERO_NOTICE => 'Hero Banner',
            CmsHomepageBanner::TYPE_ALERT => 'Alert Banner',
            CmsHomepageBanner::TYPE_FEATURE => 'Feature Banner',
        ];
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::labels());
    }

    public static function label(?string $type): string
    {
        return self::labels()[$type] ?? 'Banner';
    }

    public static function isValid(?string $type): bool
    {
        return in_array((string) $type, self::keys(), true);
    }
}
