<?php

declare(strict_types=1);

namespace App\Http\Users\UpdateUser;

use App\Concerns\ProfileValidationRules;
use App\Domain\Users\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * B16 — an admin changes a user's name and role; the address is the user's own.
 */
final class UpdateUserRequest extends FormRequest
{
    use ProfileValidationRules;

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => $this->nameRules(),
            'role' => ['required', Rule::enum(Role::class)],
        ];
    }

    public function toInput(): UpdateUserInput
    {
        return new UpdateUserInput(
            name: $this->string('name')->toString(),
            role: Role::from($this->string('role')->toString()),
        );
    }
}
