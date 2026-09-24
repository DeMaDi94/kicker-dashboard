<?php

namespace Database\Seeders;

use App\Domain\Users\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database: an admin and a user to sign in as,
     * both with the password `password`. Safe to run again.
     */
    public function run(): void
    {
        $this->user('Test User', 'test@example.com', Role::Admin);
        $this->user('Regular User', 'user@example.com', Role::User);
    }

    private function user(string $name, string $email, Role $role): void
    {
        $user = User::withTrashed()->where('email', $email)->first()
            ?? User::factory()->create(['name' => $name, 'email' => $email]);

        $user->syncRoles($role->value);
    }
}
