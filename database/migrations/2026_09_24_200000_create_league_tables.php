<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // PLY-01, ACC-04 — a player is not a user account.
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('alias');
            $table->timestamps();
        });

        // SEA-01 — a name and the penalty scale (PEN-01), in cents.
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('penalty_start_cents');
            $table->unsignedInteger('penalty_step_cents');
            $table->timestamps();
        });

        // SEA-02 — the players of a season.
        Schema::create('player_season', function (Blueprint $table) {
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->restrictOnDelete();
            $table->primary(['season_id', 'player_id']);
        });

        // MD-01 — one player's points on one matchday.
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('matchday');
            $table->integer('points');
            $table->timestamps();
            $table->unique(['season_id', 'matchday', 'player_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scores');
        Schema::dropIfExists('player_season');
        Schema::dropIfExists('seasons');
        Schema::dropIfExists('players');
    }
};
