<?php

namespace App\Cms;

use App\Models\CmsGalleryAlbum;
use App\Models\CmsGalleryItem;
use App\Models\CmsHomepageBanner;
use App\Models\CmsManagementProfile;
use App\Models\CmsNewsArticle;
use App\Models\CmsOfficeContact;
use App\Models\CmsProgrammeHighlight;
use App\Models\CmsPublicPartner;
use Illuminate\Database\Eloquent\Model;

final class CmsContentTypes
{
    /**
     * @var array<string, class-string<Model>>
     */
    public const MAP = [
        'news' => CmsNewsArticle::class,
        'gallery-albums' => CmsGalleryAlbum::class,
        'gallery-items' => CmsGalleryItem::class,
        'management-profiles' => CmsManagementProfile::class,
        'public-partners' => CmsPublicPartner::class,
        'programme-highlights' => CmsProgrammeHighlight::class,
        'homepage-banners' => CmsHomepageBanner::class,
        'office-contact' => CmsOfficeContact::class,
    ];

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::MAP);
    }

    /**
     * @return class-string<Model>
     */
    public static function modelClass(string $type): string
    {
        if (! isset(self::MAP[$type])) {
            abort(404);
        }

        return self::MAP[$type];
    }

    public static function morphAlias(string $class): ?string
    {
        $alias = array_search($class, self::MAP, true);

        return $alias === false ? null : $alias;
    }

    public static function reviewUrl(string $type, int $id): ?string
    {
        return match ($type) {
            'news' => route('cms.news.show', $id),
            'gallery-albums' => route('cms.gallery-albums.show', $id),
            'management-profiles' => route('cms.management-profiles.show', $id),
            'public-partners' => route('cms.public-partners.show', $id),
            'programme-highlights' => route('cms.programme-highlights.show', $id),
            'homepage-banners' => route('cms.homepage-banners.show', $id),
            'office-contact' => route('cms.office-contact.show'),
            default => null,
        };
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            'news' => 'News',
            'gallery-albums' => 'Gallery albums',
            'gallery-items' => 'Gallery items',
            'management-profiles' => 'Management team',
            'public-partners' => 'Public partners',
            'programme-highlights' => 'Programme highlights',
            'homepage-banners' => 'Homepage banners',
            'office-contact' => 'Office contact',
        ];
    }
}
