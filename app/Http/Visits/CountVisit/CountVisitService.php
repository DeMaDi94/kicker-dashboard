<?php

declare(strict_types=1);

namespace App\Http\Visits\CountVisit;

use App\Domain\Visits\Bots;
use App\Domain\Visits\PublicPage;
use App\Domain\Visits\VisitorMark;
use App\Models\Visit;
use Illuminate\Support\Facades\Cache;

/**
 * VIS-01, VIS-02 — stores one visit: the page, the time and the day's
 * visitor mark. Bots are not counted.
 */
final class CountVisitService
{
    public function __invoke(PublicPage $page, ?string $ip, ?string $userAgent): void
    {
        if ($userAgent === null || Bots::matches($userAgent)) {
            return;
        }

        $now = now();

        Visit::create([
            'page' => $page,
            'visitor' => VisitorMark::of($this->saltOf($now->toDateString()), (string) $ip, $userAgent),
            'visited_at' => $now,
        ]);
    }

    /**
     * D14 — a random salt per day, in the cache until midnight and never
     * stored with the visits. `add` keeps two first visits of the day from
     * each drawing their own.
     */
    private function saltOf(string $date): string
    {
        $key = 'visits.salt.'.$date;
        $salt = bin2hex(random_bytes(16));

        Cache::add($key, $salt, now()->addDay()->startOfDay());

        return Cache::string($key, $salt);
    }
}
