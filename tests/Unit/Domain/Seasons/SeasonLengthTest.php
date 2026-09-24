<?php

declare(strict_types=1);

use App\Domain\Seasons\SeasonLength;

it('has 34 matchdays per season (SEA-04)', function () {
    expect(SeasonLength::matchdays())->toBe(range(1, 34));
});

it('lets the interim settlement follow any matchday but the last (PEN-04)', function () {
    expect(SeasonLength::settlementMatchdays())->toBe(range(1, 33));
});
