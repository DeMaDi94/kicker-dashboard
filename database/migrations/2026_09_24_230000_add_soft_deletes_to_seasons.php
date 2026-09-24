<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

/*
 * SEA-06 — seasons are soft-deleted, as users are (B15): the scores stay and
 * an admin can restore the season. The unique name index (D8) stays, so a
 * deleted season's name remains taken. The admin permission to delete and
 * restore is frozen here like the others (B13).
 */
return new class extends Migration
{
    private const PERMISSION = 'seasons.delete';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            $table->softDeletes();
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
            $table->dropSoftDeletes();
        });
    }
};
