<?php

declare(strict_types=1);

namespace App\Http\Users\ListUsers;

use App\Domain\Users\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ListUsersRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', Rule::enum(Role::class)],
            'status' => ['nullable', Rule::in([ListUsersQuery::STATUS_DELETED])],
            'sort' => ['nullable', Rule::in(ListUsersQuery::sorts())],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
