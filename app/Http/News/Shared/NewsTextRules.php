<?php

declare(strict_types=1);

namespace App\Http\News\Shared;

use App\Domain\News\NewsText;

/**
 * NEWS-01 — the text of a news post, written or changed. D17 — at most
 * 2 000 characters.
 */
final class NewsTextRules
{
    /**
     * @return list<string>
     */
    public static function rules(): array
    {
        return ['required', 'string', 'max:'.NewsText::MAX_LENGTH];
    }
}
