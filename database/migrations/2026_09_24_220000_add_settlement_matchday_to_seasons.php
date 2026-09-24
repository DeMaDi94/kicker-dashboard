<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

/*
 * PEN-04 — the matchday after which the penalty box is settled (null: none
 * set), and the admin permission to change it later. The permission name is
 * frozen here like the others (B13).
 */
return new class extends Migration
{
    private const PERMISSION = 'seasons.set-settlement';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            $table->unsignedTinyInteger('settlement_matchday')->nullable();
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

        Schema::table('seasons', function (Blueprint $table) {
            $table->dropColumn('settlement_matchday');
        });
    }
};
