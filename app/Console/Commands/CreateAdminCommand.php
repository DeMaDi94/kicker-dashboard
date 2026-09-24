<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Users\Role;
use App\Http\Users\StoreUser\StoreUserInput;
use App\Http\Users\StoreUser\StoreUserRequest;
use App\Http\Users\StoreUser\StoreUserService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\text;

/**
 * B14 — with registration gone, the first admin of an installation is created
 * here. They get the same invitation as any user an admin creates.
 */
final class CreateAdminCommand extends Command
{
    protected $signature = 'users:create-admin';

    protected $description = 'Create an admin and send them the invitation to set a password';

    public function handle(StoreUserService $store): int
    {
        $email = text(label: 'Email address', required: true);

        $input = [
            'name' => text(label: 'Name', required: true),
            'email' => config('fortify.lowercase_usernames') ? mb_strtolower($email) : $email,
            'role' => Role::Admin->value,
        ];

        $validator = Validator::make($input, (new StoreUserRequest)->rules());

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        $store(new StoreUserInput($input['name'], $input['email'], Role::Admin));

        $this->components->info("Invitation sent to {$input['email']}.");

        return self::SUCCESS;
    }
}
