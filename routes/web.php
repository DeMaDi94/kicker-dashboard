<?php

use App\Domain\Seasons\SeasonLength;
use App\Domain\Users\Permission;
use App\Domain\Visits\PublicPage;
use App\Http\History\ShowHistory\ShowHistoryController;
use App\Http\Matchdays\EditMatchday\EditMatchdayController;
use App\Http\Matchdays\UpdateMatchday\UpdateMatchdayController;
use App\Http\News\DeleteNews\DeleteNewsController;
use App\Http\News\StoreNews\StoreNewsController;
use App\Http\News\UpdateNews\UpdateNewsController;
use App\Http\Players\CreatePlayer\CreatePlayerController;
use App\Http\Players\EditPlayer\EditPlayerController;
use App\Http\Players\ListPlayers\ListPlayersController;
use App\Http\Players\StorePlayer\StorePlayerController;
use App\Http\Players\UpdatePlayer\UpdatePlayerController;
use App\Http\Seasons\CreateSeason\CreateSeasonController;
use App\Http\Seasons\DeleteSeason\DeleteSeasonController;
use App\Http\Seasons\EditPenaltyScale\EditPenaltyScaleController;
use App\Http\Seasons\EditSeasonPlayers\EditSeasonPlayersController;
use App\Http\Seasons\EditSeasonSettlement\EditSeasonSettlementController;
use App\Http\Seasons\ListDeletedSeasons\ListDeletedSeasonsController;
use App\Http\Seasons\RestoreSeason\RestoreSeasonController;
use App\Http\Seasons\ShowSeason\ShowSeasonController;
use App\Http\Seasons\StoreSeason\StoreSeasonController;
use App\Http\Seasons\UpdatePenaltyScale\UpdatePenaltyScaleController;
use App\Http\Seasons\UpdateSeasonPlayers\UpdateSeasonPlayersController;
use App\Http\Seasons\UpdateSeasonSettlement\UpdateSeasonSettlementController;
use App\Http\Statistics\ComparePlayers\ComparePlayersController;
use App\Http\Statistics\ShowPlayer\ShowPlayerController;
use App\Http\Statistics\ShowRecords\ShowRecordsController;
use App\Http\Visits\CountVisit\CountVisitMiddleware;
use App\Http\Visits\ShowVisits\ShowVisitsController;
use Illuminate\Support\Facades\Route;

/*
 * VIS-01 — a guest's visit of a public page is counted.
 */
$countVisit = fn (PublicPage $page): string => CountVisitMiddleware::class.':'.$page->value;

// D1 / ACC-01 — `/` is the public season view; no sign-in needed to read.
Route::get('/', ShowSeasonController::class)->middleware($countVisit(PublicPage::SeasonView))->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // ACC-03 — players and seasons are created by admins only; players are never deleted.
    Route::get('players', ListPlayersController::class)
        ->can(Permission::CreatePlayers->value)->name('players.index');
    Route::get('players/create', CreatePlayerController::class)
        ->can(Permission::CreatePlayers->value)->name('players.create');
    Route::post('players', StorePlayerController::class)
        ->can(Permission::CreatePlayers->value)->name('players.store');
    // PLY-02 — an admin changes a player's name and alias.
    Route::get('players/{player}/edit', EditPlayerController::class)
        ->can(Permission::UpdatePlayers->value)->name('players.edit');
    Route::put('players/{player}', UpdatePlayerController::class)
        ->can(Permission::UpdatePlayers->value)->name('players.update');

    Route::get('seasons/create', CreateSeasonController::class)
        ->can(Permission::CreateSeasons->value)->name('seasons.create');
    Route::post('seasons', StoreSeasonController::class)
        ->can(Permission::CreateSeasons->value)->name('seasons.store');
    Route::get('seasons/{season}/players', EditSeasonPlayersController::class)
        ->can(Permission::SetSeasonPlayers->value)->name('seasons.players.edit');
    Route::put('seasons/{season}/players', UpdateSeasonPlayersController::class)
        ->can(Permission::SetSeasonPlayers->value)->name('seasons.players.update');

    // PEN-04, ACC-03 — the interim settlement changes later.
    Route::get('seasons/{season}/settlement', EditSeasonSettlementController::class)
        ->can(Permission::SetSeasonSettlement->value)->name('seasons.settlement.edit');
    Route::put('seasons/{season}/settlement', UpdateSeasonSettlementController::class)
        ->can(Permission::SetSeasonSettlement->value)->name('seasons.settlement.update');

    // SEA-05 — any signed-in user changes a season's penalty scale, at any time.
    Route::get('seasons/{season}/penalty-scale', EditPenaltyScaleController::class)->name('seasons.penalty-scale.edit');
    Route::put('seasons/{season}/penalty-scale', UpdatePenaltyScaleController::class)->name('seasons.penalty-scale.update');
    // SEA-06 — an admin deletes a season and restores it; restoring needs the same permission.
    // `seasons/deleted` comes before the public `seasons/{season}`, so the literal path wins.
    Route::get('seasons/deleted', ListDeletedSeasonsController::class)
        ->can(Permission::DeleteSeasons->value)->name('seasons.deleted');
    Route::delete('seasons/{season}', DeleteSeasonController::class)
        ->can(Permission::DeleteSeasons->value)->name('seasons.destroy');
    Route::post('seasons/{season}/restore', RestoreSeasonController::class)
        ->withTrashed()->can(Permission::DeleteSeasons->value)->name('seasons.restore');

    // ACC-02 — any signed-in user enters and changes points. SEA-04 — matchdays 1 to 34.
    Route::get('seasons/{season}/matchdays/{matchday}', EditMatchdayController::class)
        ->whereIn('matchday', array_map(strval(...), SeasonLength::matchdays()))->name('matchdays.edit');
    Route::put('seasons/{season}/matchdays/{matchday}', UpdateMatchdayController::class)
        ->whereIn('matchday', array_map(strval(...), SeasonLength::matchdays()))->name('matchdays.update');

    // NEWS-01 — any signed-in user writes a season's news; NEWS-03 — its
    // author or an admin changes or deletes it (checked by the Request).
    Route::post('seasons/{season}/news', StoreNewsController::class)->name('news.store');
    Route::put('news/{news}', UpdateNewsController::class)->name('news.update');
    Route::delete('news/{news}', DeleteNewsController::class)->name('news.destroy');

    // VIS-04 — the visit statistics, for admins only.
    Route::get('visits', ShowVisitsController::class)
        ->can(Permission::ViewVisits->value)->name('visits.index');

    // LOG-03 — the history, for admins only.
    Route::get('history', ShowHistoryController::class)
        ->can(Permission::ViewHistory->value)->name('history.index');

    /*
     * The primitive gallery: every components/core primitive in its states,
     * inside the real shell. It carries no data and is never registered in
     * production.
     */
    if (! app()->isProduction()) {
        Route::inertia('_primitives', 'primitives/index')->name('primitives');
    }
});

// ACC-01 — every season is readable without signing in.
Route::get('seasons/{season}', ShowSeasonController::class)->middleware($countVisit(PublicPage::SeasonView))->name('seasons.show');

// STAT-01, STAT-09, STAT-10 — the statistics are public too.
Route::get('players/{player}', ShowPlayerController::class)->whereNumber('player')
    ->middleware($countVisit(PublicPage::Player))->name('players.show');
Route::get('seasons/{season}/compare', ComparePlayersController::class)
    ->middleware($countVisit(PublicPage::HeadToHead))->name('seasons.compare');
Route::get('records', ShowRecordsController::class)
    ->middleware($countVisit(PublicPage::Records))->name('records');

require __DIR__.'/settings.php';
