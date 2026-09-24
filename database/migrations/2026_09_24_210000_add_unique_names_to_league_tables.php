<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * D8 — a season's name is unique; a player is unique by name and alias
 * together.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            $table->unique('name');
        });

        Schema::table('players', function (Blueprint $table) {
            $table->unique(['name', 'alias']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropUnique(['name', 'alias']);
        });

        Schema::table('seasons', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });
    }
};
