<?php

namespace App\Cms;

final class CmsPublicPartnerCategories
{
    /**
     * @return list<string>
     */
    public static function titles(): array
    {
        $titles = [];

        foreach (config('public_site.partner_categories', []) as $category) {
            if (is_array($category) && isset($category['title'])) {
                $titles[] = (string) $category['title'];
            }
        }

        return $titles;
    }

    /**
     * Official titles plus legacy values still stored on existing records.
     *
     * @return list<string>
     */
    public static function allowed(): array
    {
        return array_values(array_unique(array_merge(self::titles(), [
            'Bilateral',
            'Multilateral',
            'NGO',
            'Private Sector',
            'Other',
        ])));
    }

    /**
     * @return list<string>
     */
    public static function formOptions(?string $current = null): array
    {
        $options = self::titles();

        if ($current !== null && $current !== '' && ! in_array($current, $options, true)) {
            array_unshift($options, $current);
        }

        return $options;
    }
}
