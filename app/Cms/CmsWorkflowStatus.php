<?php

namespace App\Cms;

final class CmsWorkflowStatus
{
    public const DRAFT = 'draft';

    public const SUBMITTED = 'submitted';

    public const REJECTED = 'rejected';

    public const APPROVED = 'approved';

    public const PUBLISHED = 'published';

    public const ARCHIVED = 'archived';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::DRAFT,
            self::SUBMITTED,
            self::REJECTED,
            self::APPROVED,
            self::PUBLISHED,
            self::ARCHIVED,
        ];
    }
}
