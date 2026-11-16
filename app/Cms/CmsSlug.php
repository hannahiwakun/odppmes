<?php

namespace App\Cms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class CmsSlug
{
    /**
     * @param  class-string<Model>  $modelClass
     */
    public static function unique(string $source, string $modelClass, ?int $ignoreId = null): string
    {
        $base = Str::slug($source);
        if ($base === '') {
            $base = 'item';
        }

        $slug = $base;
        $suffix = 2;

        while (static::exists($modelClass, $slug, $ignoreId)) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return Str::limit($slug, 191, '');
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private static function exists(string $modelClass, string $slug, ?int $ignoreId): bool
    {
        $query = $modelClass::query()->withTrashed()->where('slug', $slug);

        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        return $query->exists();
    }
}
