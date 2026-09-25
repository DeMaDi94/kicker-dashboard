<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

/*
 * VIS-02 — per visit only the page, the time and the day's visitor mark; no
 * IP address, no user agent. VIS-04 — the admin permission to see them is
 * frozen here like the others (B13).
 */
return new class extends Migration
{
    private const PERMISSION = 'visits.view';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->string('page', 16);
            $table->char('visitor', 16);
            $table->dateTime('visited_at')->index();
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

        Schema::dropIfExists('visits');
    }
};
