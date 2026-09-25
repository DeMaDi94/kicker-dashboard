<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

/*
 * LOG-01, LOG-02 — who changed what, when, with the old and the new values.
 * The season's or player's name is kept as it was, so an entry still reads
 * right after a rename or a delete. LOG-03, D17 — the admin permission to see
 * it is frozen here like the others (B13).
 */
return new class extends Migration
{
    private const PERMISSION = 'history.view';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('history_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 32);
            $table->string('subject')->nullable();
            $table->unsignedTinyInteger('matchday')->nullable();
            $table->json('changes');
            $table->dateTime('recorded_at')->index();
        });

        $now = now();
        DB::table('permissions')->insert(['name' => self::PERMISSION, 'guard_name' => 'web', 'created_at' => $now, 'updated_at' => $now]);

        DB::table('role_has_permissions')->insert([
            'permission_id' => DB::table('permissions')->where('name', self::PERMISSION)->value('id'),
            'role_id' => DB::table('roles')->where('name', 'admin')->value('id'),
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')->where('name', self::PERMISSION)->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Schema::dropIfExists('history_entries');
    }
};
