<?php

declare(strict_types=1);

namespace App\Http\Seasons\ShowSeason;

/**
 * MD-07 — what a messenger shows for a link to a complete matchday: the
 * heading of the shared text (MD-06) and the day's winners and „Rote
 * Laterne“ with their points. D18 — none for any other address.
 *
 * @phpstan-import-type SeasonView from ShowSeasonService
 * @phpstan-import-type MatchdayBlock from ShowSeasonService
 *
 * @phpstan-type Preview array{title: string, description: string, url: string}
 */
final class MatchdayPreview
{
    /**
     * @param  SeasonView  $view
     * @return Preview|null
     */
    public function __invoke(array $view, int $number): ?array
    {
        $season = $view['season'];
        $matchday = array_find($view['matchdays'], fn (array $each): bool => $each['number'] === $number);

        if ($season === null || $matchday === null || $matchday['highlights'] === null) {
            return null;
        }

        return [
            'title' => __(':app :season · Matchday :number', [
                'app' => config('app.name'),
                'season' => $season['name'],
                'number' => $number,
            ]),
            'description' => implode(' · ', [
                __('Matchday winner: :names (:points points)', $this->named($matchday, $matchday['highlights']['winners'])),
                __('Rote Laterne: :names (:points points)', $this->named($matchday, $matchday['highlights']['lanterns'])),
            ]),
            'url' => route('seasons.show', ['season' => $season['id'], 'matchday' => $number]),
        ];
    }

    /**
     * Tied players share their points, so the first one's stand for all.
     *
     * @param  MatchdayBlock  $matchday
     * @param  list<array{playerId: int, name: string, alias: string}>  $players
     * @return array{names: string, points: string}
     */
    private function named(array $matchday, array $players): array
    {
        $first = array_find($matchday['rows'], fn (array $row): bool => $row['playerId'] === ($players[0]['playerId'] ?? null));

        return [
            'names' => implode(', ', array_column($players, 'name')),
            'points' => (string) ($first['points'] ?? ''),
        ];
    }
}
