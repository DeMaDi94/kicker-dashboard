<?php

declare(strict_types=1);

namespace App\Http\Users\StoreUser;

use App\Concerns\ProfileValidationRules;
use App\Domain\Users\Role;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreUserRequest extends FormRequest
{
    use ProfileValidationRules;

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => $this->nameRules(),
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class)->withoutTrashed(),
                // B15 — a deleted user's address stays taken: restore that user instead.
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (User::onlyTrashed()->where('email', $value)->exists()) {
                        $fail(__('This email address belongs to a deleted user. Restore that user instead.'));
                    }
                },
            ],
            'role' => ['required', Rule::enum(Role::class)],
        ];
    }

    /**
     * Fortify lowercases the address a user signs in with, so an account
     * must be stored lowercased to be reachable at all.
     */
    protected function prepareForValidation(): void
    {
        if (config('fortify.lowercase_usernames') && is_string($this->input('email'))) {
            $this->merge(['email' => mb_strtolower($this->input('email'))]);
        }
    }

    public function toInput(): StoreUserInput
    {
        return new StoreUserInput(
            name: $this->string('name')->toString(),
            email: $this->string('email')->toString(),
            role: Role::from($this->string('role')->toString()),
        );
    }
}
