<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Seasons\SeasonLength;
use App\Models\Player;
use App\Models\Score;
use App\Models\Season;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Development data: a season with every matchday (SEA-04) filled for all its
 * players, so the season view, the overall table and the statistics have
 * something to show. The points are random and mean nothing.
 */
final class SeedDummySeasonCommand extends Command
{
    protected $signature = 'seasons:seed-dummy
        {--name=Dummy-Saison : The name of the season}
        {--players=10 : How many players take part}';

    protected $description = 'Create a dummy season with random points on all 34 matchdays (development only)';

    public function handle(): int
    {
        if ($this->laravel->isProduction()) {
            $this->components->error('Refusing to seed dummy data in production.');

            return self::FAILURE;
        }

        $name = (string) $this->option('name');
        $playerCount = (int) $this->option('players');

        if ($playerCount < 1) {
            $this->components->error('At least one player is needed.');

            return self::FAILURE;
        }

        // D8 — the name is unique among the seasons not deleted.
        if (Season::query()->where('name', $name)->exists()) {
            $this->components->error("A season named „{$name}“ exists already.");

            return self::FAILURE;
        }

        $season = DB::transaction(function () use ($name, $playerCount): Season {
            // PEN-02 — the product owner's example scale; the settlement after the first half.
            $season = Season::query()->create([
                'name' => $name,
                'penalty_start_cents' => 450,
                'penalty_step_cents' => 50,
                'settlement_matchday' => intdiv(SeasonLength::MATCHDAYS, 2),
            ]);

            $players = Player::query()->orderBy('id')->limit($playerCount)->get();
            $players = $players->concat(Player::factory()->count($playerCount - $players->count())->create());

            $season->players()->attach($players->modelKeys());

            $now = now();
            $rows = [];

            foreach (SeasonLength::matchdays() as $matchday) {
                foreach ($players as $player) {
                    $rows[] = [
                        'season_id' => $season->id,
                        'player_id' => $player->id,
                        'matchday' => $matchday,
                        'points' => random_int(-20, 120),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            Score::query()->insert($rows);

            return $season;
        });

        $this->components->info(sprintf(
            'Season „%s“ created with %d players and %d matchdays.',
            $season->name,
            $playerCount,
            SeasonLength::MATCHDAYS,
        ));

        return self::SUCCESS;
    }
}
