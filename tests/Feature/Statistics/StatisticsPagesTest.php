<?php

declare(strict_types=1);

use App\Models\Player;
use App\Models\Score;
use App\Models\Season;
use Inertia\Testing\AssertableInertia;

/**
 * The hand-worked season of tests/Unit/Domain/Statistics/StatisticsTest.php:
 *   MD1 Anna 50 Bert 40 Cleo 30 · MD2 20 60 60 · MD3 70 10 40, scale 3,00 € / 1,00 €.
 *
 * @return array{Season, Player, Player, Player}
 */
function statsLeague(string $name = '2026/27', ?int $settlement = null): array
{
    $season = Season::factory()->create(['name' => $name, 'penalty_start_cents' => 300, 'penalty_step_cents' => 100, 'settlement_matchday' => $settlement]);
    $anna = Player::firstOrCreate(['name' => 'Anna'], ['alias' => 'anna']);
    $bert = Player::firstOrCreate(['name' => 'Bert'], ['alias' => 'bert']);
    $cleo = Player::firstOrCreate(['name' => 'Cleo'], ['alias' => 'cleo']);
    $season->players()->attach([$anna->id, $bert->id, $cleo->id]);

    foreach ([1 => [50, 40, 30], 2 => [20, 60, 60], 3 => [70, 10, 40]] as $matchday => $points) {
        foreach ([$anna, $bert, $cleo] as $i => $player) {
            Score::create(['season_id' => $season->id, 'player_id' => $player->id, 'matchday' => $matchday, 'points' => $points[$i]]);
        }
    }

    return [$season, $anna, $bert, $cleo];
}

describe('STAT-01 · a player’s own page', function () {
    it('is readable without signing in and shows the season created last', function () {
        statsLeague('2025/26');
        [$latest, $anna] = statsLeague('2026/27');

        $this->get(route('players.show', $anna))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('statistics/player')
                ->where('player.name', 'Anna')
                ->where('season.id', $latest->id)
                ->where('seasons.0.name', '2026/27')
                ->where('seasons.1.name', '2025/26')
                ->where('opponents', [['id' => Player::where('name', 'Bert')->value('id'), 'name' => 'Bert'], ['id' => Player::where('name', 'Cleo')->value('id'), 'name' => 'Cleo']]));
    });

    it('shows a chosen season, and not one the player is not in', function () {
        [$earlier, $anna] = statsLeague('2025/26');
        $other = Season::factory()->create();

        $this->get(route('players.show', ['player' => $anna, 'season' => $earlier->id]))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('season.name', '2025/26'));

        $this->get(route('players.show', ['player' => $anna, 'season' => $other->id]))->assertNotFound();
    });

    it('shows no season for a player who plays in none', function () {
        $loner = Player::factory()->create();

        $this->get(route('players.show', $loner))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('season', null)->where('stats', null)->where('career.seasonsPlayed', 0));
    });
});

// STAT-03 – STAT-07 on the page, from the hand-worked season.
it('carries the season’s figures, lines, achievements and form (STAT-03, STAT-04, STAT-05, STAT-06, STAT-07)', function () {
    [, $anna] = statsLeague(settlement: 1);

    $this->get(route('players.show', $anna))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('stats.totalPoints', 140)
            ->where('stats.averagePoints', 46.7)
            ->where('stats.bestPoints', 70)
            ->where('stats.bestMatchdays', [3])
            ->where('stats.place', 1)
            ->where('stats.firstHalfPenaltyCents', 100)
            ->where('stats.secondHalfPenaltyCents', 400)
            ->where('stats.lines.1.leagueAverage', 46.7)
            ->where('stats.lines.1.overallPlace', 3)
            ->where('stats.lines.2.cumulativePenaltyCents', 500)
            ->where('stats.wins', 2)
            ->where('stats.lanterns', 1)
            ->where('stats.form.1.grade', 'bad')
            ->where('season.settlementMatchday', 1));
});

it('adds up the player’s seasons (STAT-08)', function () {
    statsLeague('2025/26');
    [, $anna] = statsLeague('2026/27');

    $this->get(route('players.show', $anna))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('career.seasonsPlayed', 2)
            ->where('career.totalPoints', 280)
            ->where('career.totalWins', 4)
            ->where('career.averagePlace', 1));
});

describe('STAT-09 · the head-to-head', function () {
    it('compares two players of the season', function () {
        [$season, $anna, $bert] = statsLeague();

        $this->get(route('seasons.compare', ['season' => $season, 'a' => $anna->id, 'b' => $bert->id]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('statistics/compare')
                ->where('duel.aAhead', 2)
                ->where('duel.level', 0)
                ->where('duel.bAhead', 1)
                ->has('players', 3));
    });

    it('chooses nobody outside the season', function () {
        [$season, $anna] = statsLeague();
        $stranger = Player::factory()->create();

        $this->get(route('seasons.compare', ['season' => $season, 'a' => $anna->id, 'b' => $stranger->id]))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('b', null)->where('duel', null));
    });
});

describe('STAT-10 · league records', function () {
    it('shows the records over all seasons, readable without signing in', function () {
        statsLeague('2025/26');
        statsLeague('2026/27');

        $this->get(route('records'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('statistics/records')
                ->where('season', null)
                ->where('records.highestScore.value', 70)
                ->has('records.highestScore.holders', 2));
    });

    it('shows the most expensive matchday as a record (STAT-15)', function () {
        statsLeague();

        $this->get(route('records'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('records.mostExpensiveMatchday.value', 700)
                ->where('records.mostExpensiveMatchday.holders.0.matchday', 2)
                ->where('records.mostExpensiveMatchday.holders.0.playerId', null)
                ->has('records.mostExpensiveMatchday.holders', 1));
    });

    it('narrows to one season, and knows no other', function () {
        [$season] = statsLeague('2026/27');
        statsLeague('2025/26');

        $this->get(route('records', ['season' => $season->id]))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('season', $season->id)
                ->has('records.highestScore.holders', 1)
                ->where('records.highestScore.holders.0.seasonName', '2026/27'));

        $this->get(route('records', ['season' => 9999]))->assertNotFound();
    });
});

it('names each matchday’s winners, „Rote Laterne“ and average in the season view (STAT-11)', function () {
    statsLeague();

    $this->get(route('home'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('matchdays.1.highlights.winners.0.name', 'Bert')
            ->where('matchdays.1.highlights.winners.1.name', 'Cleo')
            ->where('matchdays.1.highlights.lanterns.0.name', 'Anna')
            ->where('matchdays.1.highlights.average', 46.7)
            ->where('matchdays.5.highlights', null));
});

it('shows the season’s penalty box in the season view (STAT-12)', function () {
    statsLeague(settlement: 1);

    $this->get(route('home'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('penaltyBox.totalCents', 1900)
            ->where('penaltyBox.firstHalfCents', 600)
            ->where('penaltyBox.secondHalfCents', 1300)
            ->where('penaltyBox.payers.0.name', 'Bert')
            ->where('penaltyBox.payers.2.name', 'Anna'));
});

it('shows the money each complete matchday put into the box in the season view (STAT-13)', function () {
    statsLeague();

    $this->get(route('home'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('matchdays.0.highlights.penaltyCents', 600)
            ->where('matchdays.1.highlights.penaltyCents', 700)
            ->where('matchdays.2.highlights.penaltyCents', 600)
            ->where('matchdays.3.highlights', null));
});

it('carries the penalty box graph’s money per matchday and balance, with the settlement (STAT-14)', function () {
    statsLeague(settlement: 2);

    $this->get(route('home'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('season.settlementMatchday', 2)
            ->has('penaltyBox.matchdays', 3)
            ->where('penaltyBox.matchdays.1', ['matchday' => 2, 'cents' => 700, 'cumulativeCents' => 1300])
            ->where('penaltyBox.matchdays.2.cumulativeCents', 1900));
});
