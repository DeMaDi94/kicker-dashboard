<?php

declare(strict_types=1);

namespace App\Domain\News;

/**
 * NEWS-01 — a news post is plain text. D17 — at most 2 000 characters.
 */
final class NewsText
{
    public const MAX_LENGTH = 2000;
}
