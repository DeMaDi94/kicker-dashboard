<?php

declare(strict_types=1);

use App\Domain\Seasons\SeasonLength;

it('has 34 matchdays per season (SEA-04)', function () {
    expect(SeasonLength::matchdays())->toBe(range(1, 34));
});
