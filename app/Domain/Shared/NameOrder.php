<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use Collator;

/**
 * D3 — the last tie-break of every table: the player's name A–Z, read the
 * German way (umlauts beside their base letter, case ignored).
 */
final class NameOrder
{
    private static ?Collator $collator = null;

    public static function compare(string $a, string $b): int
    {
        self::$collator ??= new Collator('de');

        return (int) self::$collator->compare($a, $b);
    }
}
