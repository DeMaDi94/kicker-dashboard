<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Users\Role;
use App\Http\Users\StoreUser\StoreUserInput;
use App\Http\Users\StoreUser\StoreUserRequest;
use App\Http\Users\StoreUser\StoreUserService;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

/**
 * B14 — with registration gone, the first admin of an installation is created
 * here. They get the same invitation as any user an admin creates. The options
 * exist for hosts that run commands without a terminal (Laravel Cloud), where
 * the prompts cannot ask.
 *
 * D16 — while outgoing mail is switched off, no invitation can go out: the
 * command takes a password instead and sets it as an admin would (D15).
 */
final class CreateAdminCommand extends Command
{
    protected $signature = 'users:create-admin {--email= : The admin\'s email address} {--name= : The admin\'s name} {--password= : The admin\'s password, used while outgoing mail is switched off}';

    protected $description = 'Create an admin and send them the invitation to set a password (or set it, while mail is off)';

    public function handle(StoreUserService $store): int
    {
        $email = $this->stringOption('email') ?? text(label: 'Email address', required: true);
        $name = $this->stringOption('name') ?? text(label: 'Name', required: true);

        $newPassword = Setting::mailEnabled() ? null : ($this->stringOption('password') ?? password(label: 'Password', required: true));

        $input = [
            'name' => $name,
            'email' => config('fortify.lowercase_usernames') ? mb_strtolower($email) : $email,
            'role' => Role::Admin->value,
            'password_setup' => $newPassword === null ? 'invitation' : 'password',
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ];

        $validator = Validator::make($input, (new StoreUserRequest)->rules());

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        $store(new StoreUserInput($input['name'], $input['email'], Role::Admin, $newPassword));

        $this->components->info($newPassword === null
            ? "Invitation sent to {$input['email']}."
            : "Admin {$input['email']} created with the password given.");

        return self::SUCCESS;
    }

    private function stringOption(string $key): ?string
    {
        $value = $this->option($key);

        return is_string($value) && $value !== '' ? $value : null;
    }
}
