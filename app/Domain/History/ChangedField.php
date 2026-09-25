<?php

declare(strict_types=1);

namespace App\Domain\History;

/**
 * LOG-02 — which value of an entry changed. The stored value is the wire
 * format; the client labels and formats each one.
 */
enum ChangedField: string
{
    case Name = 'name';
    case Alias = 'alias';
    case Points = 'points';
    case PenaltyStart = 'penaltyStart';
    case PenaltyStep = 'penaltyStep';
    case SettlementMatchday = 'settlementMatchday';
    case Players = 'players';
    case Text = 'text';
}
