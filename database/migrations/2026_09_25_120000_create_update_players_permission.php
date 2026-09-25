<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/*
 * PLY-02, D17 — an admin changes a player's name and alias. Frozen like the
 * other permissions (B13).
 */
return new class extends Migration
{
    private const PERMISSION = 'players.update';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
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
    }
};
