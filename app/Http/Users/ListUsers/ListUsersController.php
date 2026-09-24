<?php

declare(strict_types=1);

namespace App\Http\Users\ListUsers;

use App\Domain\Users\Role;
use Inertia\Inertia;
use Inertia\Response;

final class ListUsersController
{
    public function __invoke(ListUsersRequest $request, ListUsersService $list): Response
    {
        /** @var array{search?: string|null, role?: string|null, status?: string|null, sort?: string|null, page?: int|string|null} $input */
        $input = $request->validated();
        $query = ListUsersQuery::from($input);

        return Inertia::render('settings/users/index', [
            ...$list($query),
            'filters' => $query->filters(),
            'roles' => array_column(Role::cases(), 'value'),
        ]);
    }
}
