<?php

declare(strict_types=1);

namespace App\Domain\News;

/**
 * NEWS-03 — a news post is changed or deleted by its author and by any admin
 * (D17: the permission `news.manage`).
 */
final class NewsAccess
{
    public static function mayChange(int $authorId, int $userId, bool $mayManageNews): bool
    {
        return $mayManageNews || $authorId === $userId;
    }
}
