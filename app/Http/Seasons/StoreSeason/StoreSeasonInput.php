<?php

declare(strict_types=1);

namespace App\Http\Seasons\StoreSeason;

final readonly class StoreSeasonInput
{
    /**
     * @param  list<int>  $playerIds
     */
    public function __construct(
        public string $name,
        public int $penaltyStartCents,
        public int $penaltyStepCents,
        public array $playerIds,
    ) {}
}
