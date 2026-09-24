<?php

declare(strict_types=1);

namespace App\Http\Statistics\Shared;

use App\Domain\Statistics\SeasonTimeline;
use App\Models\Player;
use App\Models\Score;
use App\Models\Season;
use Illuminate\Database\Eloquent\Builder;

/**
 * Seasons read into the statistics' timeline (STAT-02), newest first — two
 * queries however many seasons there are.
 */
final class SeasonTimelines
{
    /**
     * @return array<int, array{name: string, timeline: SeasonTimeline}> season id => season
     */
    public function all(): array
    {
        return $this->load(Season::query());
    }

    public function forSeason(Season $season): SeasonTimeline
    {
        return $this->load(Season::query()->whereKey($season->id))[$season->id]['timeline'];
    }

    /**
     * @return array<int, array{name: string, timeline: SeasonTimeline}> season id => season
     */
    public function forPlayer(Player $player): array
    {
        return $this->load(Season::query()->whereHas('players', fn (Builder $players) => $players->whereKey($player->id)));
    }

    /**
     * @param  Builder<Season>  $query
     * @return array<int, array{name: string, timeline: SeasonTimeline}>
     */
    private function load(Builder $query): array
    {
        $seasons = [];

        foreach ($query->with(['players:id,name', 'scores:id,season_id,player_id,matchday,points'])->latest('id')->get() as $season) {
            $pointsByMatchday = [];
            foreach ($season->scores as $score) {
                /** @var Score $score */
                $pointsByMatchday[$score->matchday][$score->player_id] = $score->points;
            }

            $participants = [];
            foreach ($season->players as $player) {
                /** @var Player $player */
                $participants[$player->id] = $player->name;
            }

            $seasons[$season->id] = [
                'name' => $season->name,
                'timeline' => new SeasonTimeline($participants, $pointsByMatchday, $season->penaltyScale(), $season->settlement_matchday),
            ];
        }

        return $seasons;
    }
}
