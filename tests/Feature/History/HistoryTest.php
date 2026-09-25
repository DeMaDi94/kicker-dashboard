<?php

declare(strict_types=1);

use App\Domain\History\HistoryAction;
use App\Models\HistoryEntry;
use App\Models\NewsItem;
use App\Models\Player;
use App\Models\Score;
use App\Models\Season;
use App\Models\User;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia;

/**
 * A season „2025/26“ of BK and FK, penalty scale 4,50 € / 0,50 €.
 *
 * @return array{Season, Player, Player}
 */
function historySeason(): array
{
    $season = Season::factory()->create(['name' => '2025/26', 'penalty_start_cents' => 450, 'penalty_step_cents' => 50, 'settlement_matchday' => null]);
    $bk = Player::factory()->create(['name' => 'BK', 'alias' => 'bk']);
    $fk = Player::factory()->create(['name' => 'FK', 'alias' => 'fk']);
    $season->players()->sync([$bk->id, $fk->id]);

    return [$season, $bk, $fk];
}

/**
 * @return array{user: int|null, action: HistoryAction, subject: string|null, matchday: int|null, changes: list<array{field: string, subject: string|null, old: int|string|null, new: int|string|null}>}
 */
function lastEntry(): array
{
    $entry = HistoryEntry::query()->latest('id')->firstOrFail();

    return ['user' => $entry->user_id, 'action' => $entry->action, 'subject' => $entry->subject, 'matchday' => $entry->matchday, 'changes' => $entry->changes];
}

describe('LOG-01, LOG-02 · every change to the league’s data is recorded, with who, what and each value old → new', function () {
    it('records entering points, one entry per save (MD-01, D17)', function () {
        [$season, $bk, $fk] = historySeason();
        $user = member();

        $this->actingAs($user)->put(route('matchdays.update', [$season, 7]), ['points' => [$bk->id => 62, $fk->id => 40]]);

        expect(HistoryEntry::count())->toBe(1)
            ->and(lastEntry())->toBe([
                'user' => $user->id,
                'action' => HistoryAction::PointsSaved,
                'subject' => '2025/26',
                'matchday' => 7,
                'changes' => [
                    ['field' => 'points', 'subject' => 'BK', 'old' => null, 'new' => 62],
                    ['field' => 'points', 'subject' => 'FK', 'old' => null, 'new' => 40],
                ],
            ]);
    });

    it('records changing points with only the points that changed — „BK, Spieltag 7: 62 → 65“ (MD-04)', function () {
        [$season, $bk, $fk] = historySeason();
        Score::create(['season_id' => $season->id, 'player_id' => $bk->id, 'matchday' => 7, 'points' => 62]);
        Score::create(['season_id' => $season->id, 'player_id' => $fk->id, 'matchday' => 7, 'points' => 40]);

        $this->actingAs(member())->put(route('matchdays.update', [$season, 7]), ['points' => [$bk->id => 65, $fk->id => 40]]);

        expect(lastEntry()['changes'])->toBe([['field' => 'points', 'subject' => 'BK', 'old' => 62, 'new' => 65]]);
    });

    it('records nothing for a save that changed no points', function () {
        [$season, $bk, $fk] = historySeason();
        Score::create(['season_id' => $season->id, 'player_id' => $bk->id, 'matchday' => 7, 'points' => 62]);
        Score::create(['season_id' => $season->id, 'player_id' => $fk->id, 'matchday' => 7, 'points' => 40]);

        $this->actingAs(member())->put(route('matchdays.update', [$season, 7]), ['points' => [$bk->id => 62, $fk->id => 40]]);

        expect(HistoryEntry::count())->toBe(0);
    });

    it('records creating a player (PLY-01)', function () {
        $this->actingAs(admin())->post(route('players.store'), ['name' => 'BK', 'alias' => 'bk']);

        expect(lastEntry())->toMatchArray([
            'action' => HistoryAction::PlayerCreated,
            'subject' => 'BK',
            'changes' => [
                ['field' => 'name', 'subject' => null, 'old' => null, 'new' => 'BK'],
                ['field' => 'alias', 'subject' => null, 'old' => null, 'new' => 'bk'],
            ],
        ]);
    });

    it('records changing a player (PLY-02)', function () {
        $player = Player::factory()->create(['name' => 'BK', 'alias' => 'bk']);

        $this->actingAs(admin())->put(route('players.update', $player), ['name' => 'Bernd', 'alias' => 'bk']);

        expect(lastEntry())->toMatchArray([
            'action' => HistoryAction::PlayerUpdated,
            'subject' => 'Bernd',
            'changes' => [['field' => 'name', 'subject' => null, 'old' => 'BK', 'new' => 'Bernd']],
        ]);
    });

    it('records creating a season with its scale, settlement and players (SEA-01, SEA-02, PEN-04)', function () {
        $bk = Player::factory()->create(['name' => 'BK']);
        $fk = Player::factory()->create(['name' => 'FK']);

        $this->actingAs(admin())->post(route('seasons.store'), [
            'name' => '2026/27',
            'penalty_start' => '5.00',
            'penalty_step' => '0.50',
            'settlement_matchday' => 17,
            'player_ids' => [$fk->id, $bk->id],
        ])->assertValid();

        expect(lastEntry())->toMatchArray([
            'action' => HistoryAction::SeasonCreated,
            'subject' => '2026/27',
            'changes' => [
                ['field' => 'penaltyStart', 'subject' => null, 'old' => null, 'new' => 500],
                ['field' => 'penaltyStep', 'subject' => null, 'old' => null, 'new' => 50],
                ['field' => 'settlementMatchday', 'subject' => null, 'old' => null, 'new' => 17],
                ['field' => 'players', 'subject' => null, 'old' => null, 'new' => 'BK, FK'],
            ],
        ]);
    });

    it('records deleting and restoring a season (SEA-06)', function () {
        [$season] = historySeason();
        $admin = admin();

        $this->actingAs($admin)->delete(route('seasons.destroy', $season));
        expect(lastEntry())->toMatchArray(['action' => HistoryAction::SeasonDeleted, 'subject' => '2025/26', 'changes' => []]);

        $this->actingAs($admin)->post(route('seasons.restore', $season));
        expect(lastEntry())->toMatchArray(['action' => HistoryAction::SeasonRestored, 'subject' => '2025/26', 'changes' => []]);
    });

    it('records nothing for a restore that was refused (D8)', function () {
        [$season] = historySeason();
        $season->delete();
        Season::factory()->create(['name' => '2025/26']);

        $this->actingAs(admin())->post(route('seasons.restore', $season));

        expect(HistoryEntry::count())->toBe(0);
    });

    it('records changing a season’s players (SEA-02)', function () {
        [$season, $bk] = historySeason();
        $jls = Player::factory()->create(['name' => 'JLS']);

        $this->actingAs(admin())->put(route('seasons.players.update', $season), ['player_ids' => [$bk->id, $jls->id]]);

        expect(lastEntry())->toMatchArray([
            'action' => HistoryAction::SeasonPlayersChanged,
            'changes' => [['field' => 'players', 'subject' => null, 'old' => 'BK, FK', 'new' => 'BK, JLS']],
        ]);
    });

    it('records changing the penalty scale (SEA-05)', function () {
        [$season] = historySeason();

        $this->actingAs(member())->put(route('seasons.penalty-scale.update', $season), ['penalty_start' => '5.00', 'penalty_step' => '0.50'])->assertValid();

        expect(lastEntry())->toMatchArray([
            'action' => HistoryAction::PenaltyScaleChanged,
            'subject' => '2025/26',
            'changes' => [['field' => 'penaltyStart', 'subject' => null, 'old' => 450, 'new' => 500]],
        ]);
    });

    it('records moving and removing the interim settlement (PEN-04)', function () {
        [$season] = historySeason();
        $admin = admin();

        $this->actingAs($admin)->put(route('seasons.settlement.update', $season), ['settlement_matchday' => 17]);
        expect(lastEntry())->toMatchArray([
            'action' => HistoryAction::SettlementChanged,
            'changes' => [['field' => 'settlementMatchday', 'subject' => null, 'old' => null, 'new' => 17]],
        ]);

        $this->actingAs($admin)->put(route('seasons.settlement.update', $season), ['settlement_matchday' => null]);
        expect(lastEntry()['changes'])->toBe([['field' => 'settlementMatchday', 'subject' => null, 'old' => 17, 'new' => null]]);
    });

    it('records writing, changing and deleting news (NEWS-01, NEWS-03)', function () {
        [$season] = historySeason();
        $user = member();

        $this->actingAs($user)->post(route('news.store', $season), ['text' => 'Alt']);
        expect(lastEntry())->toMatchArray([
            'user' => $user->id,
            'action' => HistoryAction::NewsCreated,
            'subject' => '2025/26',
            'changes' => [['field' => 'text', 'subject' => null, 'old' => null, 'new' => 'Alt']],
        ]);

        $news = NewsItem::sole();
        $this->actingAs($user)->put(route('news.update', $news), ['text' => 'Neu']);
        expect(lastEntry())->toMatchArray([
            'action' => HistoryAction::NewsUpdated,
            'changes' => [['field' => 'text', 'subject' => null, 'old' => 'Alt', 'new' => 'Neu']],
        ]);

        $this->actingAs($user)->delete(route('news.destroy', $news));
        expect(lastEntry())->toMatchArray([
            'action' => HistoryAction::NewsDeleted,
            'changes' => [['field' => 'text', 'subject' => null, 'old' => 'Neu', 'new' => null]],
        ]);
    });

    it('records no change to user accounts or settings', function () {
        $this->actingAs(admin())->post(route('users.store'), [
            'name' => 'Neu',
            'email' => 'neu@example.com',
            'role' => 'user',
            'password_setup' => 'password',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertValid();

        expect(User::where('email', 'neu@example.com')->exists())->toBeTrue()
            ->and(HistoryEntry::count())->toBe(0);
    });
});

describe('LOG-03 · the history, for admins only', function () {
    it('shows the entries newest first, with the user’s name and the time in German time', function () {
        $user = member(['name' => 'Dennis']);
        HistoryEntry::create(['user_id' => $user->id, 'action' => HistoryAction::SeasonDeleted, 'subject' => 'Alt', 'changes' => [], 'recorded_at' => Carbon::parse('2026-09-01 10:00')]);
        HistoryEntry::create(['user_id' => $user->id, 'action' => HistoryAction::SeasonRestored, 'subject' => 'Neu', 'changes' => [], 'recorded_at' => Carbon::parse('2026-09-20 10:00')]);

        $this->actingAs(admin())->get(route('history.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('history/index')
                ->where('entries.0.subject', 'Neu')
                ->where('entries.0.userName', 'Dennis')
                ->where('entries.0.action', 'season.restored')
                ->where('entries.0.recordedAt', '2026-09-20T10:00:00+02:00')
                ->where('entries.1.subject', 'Alt'));
    });

    it('still names a user whose account was deleted (B15)', function () {
        $user = member(['name' => 'Dennis']);
        HistoryEntry::create(['user_id' => $user->id, 'action' => HistoryAction::SeasonDeleted, 'subject' => 'X', 'changes' => [], 'recorded_at' => now()]);
        $user->delete();

        $this->actingAs(admin())->get(route('history.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('entries.0.userName', 'Dennis'));
    });

    it('shows twenty entries per page (D17)', function () {
        foreach (range(1, 21) as $i) {
            HistoryEntry::create(['user_id' => null, 'action' => HistoryAction::SeasonDeleted, 'subject' => "S{$i}", 'changes' => [], 'recorded_at' => Carbon::parse('2026-09-01')->addMinutes($i)]);
        }

        $this->actingAs(admin())->get(route('history.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('entries', 20)
                ->where('entries.0.subject', 'S21')
                ->where('pagination', ['page' => 1, 'lastPage' => 2, 'total' => 21]));

        $this->actingAs(admin())->get(route('history.index', ['page' => 2]))
            ->assertInertia(fn (AssertableInertia $page) => $page->has('entries', 1)->where('entries.0.subject', 'S1'));
    });

    it('refuses a plain user and sends a guest to the login', function () {
        $this->actingAs(member())->get(route('history.index'))->assertForbidden();
        auth()->logout();
        $this->get(route('history.index'))->assertRedirect(route('login'));
    });

    it('offers no way to delete an entry', function () {
        $entry = HistoryEntry::create(['user_id' => null, 'action' => HistoryAction::SeasonDeleted, 'subject' => 'X', 'changes' => [], 'recorded_at' => now()]);

        $this->actingAs(admin())->delete("/history/{$entry->id}")->assertNotFound();
        $this->actingAs(admin())->delete('/history')->assertMethodNotAllowed();

        expect(HistoryEntry::count())->toBe(1);
    });

    it('is not pruned', function () {
        expect(class_uses_recursive(HistoryEntry::class))->not->toHaveKey(MassPrunable::class)
            ->and(class_uses_recursive(HistoryEntry::class))->not->toHaveKey(Prunable::class);
    });

    it('shares the permission with admins only', function () {
        expect(User::factory()->make()->can('history.view'))->toBeFalse()
            ->and(admin()->can('history.view'))->toBeTrue()
            ->and(member()->can('history.view'))->toBeFalse();
    });
});
