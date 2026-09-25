<?php

declare(strict_types=1);

namespace App\Domain\Visits;

/**
 * VIS-01 — the public pages whose visits are counted, in the order the
 * requirement names them; that order also breaks a tie in the page list
 * (D14). The stored value is the wire format; the label is a translation key
 * on the client.
 */
enum PublicPage: string
{
    case SeasonView = 'season';
    case Player = 'player';
    case HeadToHead = 'compare';
    case Records = 'records';
}
