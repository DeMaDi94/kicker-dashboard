<?php

declare(strict_types=1);

namespace App\Domain\Visits;

use DateTimeImmutable;

/**
 * One stored visit, as the statistics read it (VIS-02): the page, the local
 * time (Europe/Berlin, VIS-04) and the visitor mark of that day.
 */
final readonly class VisitRow
{
    public function __construct(
        public PublicPage $page,
        public DateTimeImmutable $at,
        public string $visitor,
    ) {}
}
