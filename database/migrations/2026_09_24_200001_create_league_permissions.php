<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/*
 * ACC-03 — only admins create players and seasons and set a season's players.
 * Frozen like the users' permissions (B13); App\Domain\Users\Permission holds
 * the same values, and tests/Feature/Users/RolesAndPermissionsTest.php keeps
 * the two together.
 */
return new class extends Migration
{
    private const PERMISSIONS = ['players.create', 'seasons.create', 'seasons.set-players'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        foreach (self::PERMISSIONS as $permission) {
            DB::table('permissions')->insert(['name' => $permission, 'guard_name' => 'web', 'created_at' => $now, 'updated_at' => $now]);
        }

        $adminId = DB::table('roles')->where('name', 'admin')->value('id');

        foreach (DB::table('permissions')->whereIn('name', self::PERMISSIONS)->pluck('id') as $permissionId) {
            DB::table('role_has_permissions')->insert(['permission_id' => $permissionId, 'role_id' => $adminId]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')->whereIn('name', self::PERMISSIONS)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
