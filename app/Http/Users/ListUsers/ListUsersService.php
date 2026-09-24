<?php

declare(strict_types=1);

namespace App\Http\Users\ListUsers;

use App\Http\Users\Shared\UserRow;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * @phpstan-import-type UserRowShape from UserRow
 */
final class ListUsersService
{
    /** B16 — twenty users per page. */
    public const PAGE_SIZE = 20;

    /**
     * @return array{users: list<UserRowShape>, pagination: array{page: int, lastPage: int, total: int}}
     */
    public function __invoke(ListUsersQuery $query): array
    {
        $users = User::query()
            ->select('users.*')
            ->with('roles')
            ->when($query->deleted, fn (Builder $builder) => $builder->onlyTrashed())
            ->when($query->role !== null, fn (Builder $builder) => $builder->role($query->role->value ?? ''))
            ->when($query->search !== '', fn (Builder $builder) => $builder->where(
                fn (Builder $where) => $where
                    ->whereLike('users.name', "%{$query->search}%")
                    ->orWhereLike('users.email', "%{$query->search}%"),
            ));

        $this->sort($users, $query->sort);

        $page = $users->paginate(self::PAGE_SIZE, page: $query->page);

        return [
            'users' => array_values(array_map(UserRow::from(...), $page->items())),
            'pagination' => [
                'page' => $page->currentPage(),
                'lastPage' => $page->lastPage(),
                'total' => $page->total(),
            ],
        ];
    }

    /**
     * @param  Builder<User>  $users
     */
    private function sort(Builder $users, string $sort): void
    {
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';

        match (ltrim($sort, '-')) {
            'email' => $users->orderBy('users.email', $direction),
            'verified' => $users->orderBy('users.email_verified_at', $direction),
            'role' => $users
                ->leftJoin('model_has_roles', fn ($join) => $join
                    ->on('model_has_roles.model_id', '=', 'users.id')
                    ->where('model_has_roles.model_type', (new User)->getMorphClass()))
                ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->orderBy('roles.name', $direction),
            default => $users->orderBy('users.name', $direction),
        };

        // A stable order under every sort, so a page never repeats or skips a row.
        $users->orderBy('users.name')->orderBy('users.id');
    }
}
