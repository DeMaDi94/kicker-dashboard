<?php

use App\Domain\Seasons\SeasonLength;
use App\Domain\Users\Permission;
use App\Http\Matchdays\EditMatchday\EditMatchdayController;
use App\Http\Matchdays\UpdateMatchday\UpdateMatchdayController;
use App\Http\Players\CreatePlayer\CreatePlayerController;
use App\Http\Players\ListPlayers\ListPlayersController;
use App\Http\Players\StorePlayer\StorePlayerController;
use App\Http\Seasons\CreateSeason\CreateSeasonController;
use App\Http\Seasons\EditPenaltyScale\EditPenaltyScaleController;
use App\Http\Seasons\EditSeasonPlayers\EditSeasonPlayersController;
use App\Http\Seasons\EditSeasonSettlement\EditSeasonSettlementController;
use App\Http\Seasons\ShowSeason\ShowSeasonController;
use App\Http\Seasons\StoreSeason\StoreSeasonController;
use App\Http\Seasons\UpdatePenaltyScale\UpdatePenaltyScaleController;
use App\Http\Seasons\UpdateSeasonPlayers\UpdateSeasonPlayersController;
use App\Http\Seasons\UpdateSeasonSettlement\UpdateSeasonSettlementController;
use App\Http\Statistics\ComparePlayers\ComparePlayersController;
use App\Http\Statistics\ShowPlayer\ShowPlayerController;
use App\Http\Statistics\ShowRecords\ShowRecordsController;
use Illuminate\Support\Facades\Route;

// D1 / ACC-01 — `/` is the public season view; no sign-in needed to read.
Route::get('/', ShowSeasonController::class)->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // ACC-03 — players and seasons are created, never edited or deleted, by admins only.
    Route::get('players', ListPlayersController::class)
        ->can(Permission::CreatePlayers->value)->name('players.index');
    Route::get('players/create', CreatePlayerController::class)
        ->can(Permission::CreatePlayers->value)->name('players.create');
    Route::post('players', StorePlayerController::class)
        ->can(Permission::CreatePlayers->value)->name('players.store');

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

    // ACC-02 — any signed-in user enters and changes points. SEA-04 — matchdays 1 to 34.
    Route::get('seasons/{season}/matchdays/{matchday}', EditMatchdayController::class)
        ->whereIn('matchday', array_map(strval(...), SeasonLength::matchdays()))->name('matchdays.edit');
    Route::put('seasons/{season}/matchdays/{matchday}', UpdateMatchdayController::class)
        ->whereIn('matchday', array_map(strval(...), SeasonLength::matchdays()))->name('matchdays.update');

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
Route::get('seasons/{season}', ShowSeasonController::class)->name('seasons.show');

// STAT-01, STAT-09, STAT-10 — the statistics are public too.
Route::get('players/{player}', ShowPlayerController::class)->whereNumber('player')->name('players.show');
Route::get('seasons/{season}/compare', ComparePlayersController::class)->name('seasons.compare');
Route::get('records', ShowRecordsController::class)->name('records');

require __DIR__.'/settings.php';
