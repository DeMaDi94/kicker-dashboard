<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/*
 * B13 — the roles and permissions every environment needs, so production has
 * them after `migrate` without a seeder. The values are frozen here on
 * purpose: App\Domain\Users\Role and Permission may grow, a migration may not
 * change. tests/Feature/Users/RolesAndPermissionsTest.php holds the two
 * together.
 */
return new class extends Migration
{
    private const PERMISSIONS = ['users.view', 'users.create', 'users.update', 'users.delete'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        foreach (['admin', 'user'] as $role) {
            DB::table('roles')->insert(['name' => $role, 'guard_name' => 'web', 'created_at' => $now, 'updated_at' => $now]);
        }

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
        DB::table('roles')->whereIn('name', ['admin', 'user'])->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
