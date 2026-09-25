<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

/**
 * D13 — sets a user's password from the server when the invitation mail
 * cannot reach them. Whoever runs it vouches for the address, so it counts as
 * verified, as a password set through the emailed link does (B14).
 */
final class SetPasswordCommand extends Command
{
    protected $signature = 'users:set-password {--email= : The user\'s email address} {--password= : The new password}';

    protected $description = 'Set the password of an existing user and mark their address verified';

    public function handle(): int
    {
        $email = $this->stringOption('email') ?? text(label: 'Email address', required: true);
        $newPassword = $this->stringOption('password') ?? password(label: 'New password', required: true);

        $user = User::firstWhere('email', config('fortify.lowercase_usernames') ? mb_strtolower($email) : $email);

        if ($user === null) {
            $this->components->error("No user with the address {$email}.");

            return self::FAILURE;
        }

        $validator = Validator::make(['password' => $newPassword], ['password' => ['required', 'string', Password::default()]]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        $user->forceFill([
            'password' => $newPassword,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        $this->components->info("Password set for {$user->email}.");

        return self::SUCCESS;
    }

    private function stringOption(string $key): ?string
    {
        $value = $this->option($key);

        return is_string($value) && $value !== '' ? $value : null;
    }
}
