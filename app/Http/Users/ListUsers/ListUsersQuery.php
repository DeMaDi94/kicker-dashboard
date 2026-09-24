<?php

declare(strict_types=1);

namespace App\Http\Users\ListUsers;

use App\Domain\Users\Role;

/**
 * What the user list is asked for (B16). A sort is a column key, prefixed
 * with `-` for descending.
 */
final readonly class ListUsersQuery
{
    public const STATUS_DELETED = 'deleted';

    /** B16 — the list opens sorted by name, A–Z. */
    public const DEFAULT_SORT = 'name';

    /** B16 — every column shown is sortable. */
    public const COLUMNS = ['name', 'email', 'role', 'verified'];

    public function __construct(
        public string $search,
        public ?Role $role,
        public bool $deleted,
        public string $sort,
        public int $page,
    ) {}

    /**
     * @return list<string>
     */
    public static function sorts(): array
    {
        return [...self::COLUMNS, ...array_map(fn (string $column): string => "-{$column}", self::COLUMNS)];
    }

    /**
     * @param  array{search?: string|null, role?: string|null, status?: string|null, sort?: string|null, page?: int|string|null}  $input
     */
    public static function from(array $input): self
    {
        return new self(
            search: trim($input['search'] ?? ''),
            role: Role::tryFrom($input['role'] ?? ''),
            deleted: ($input['status'] ?? null) === self::STATUS_DELETED,
            sort: $input['sort'] ?? self::DEFAULT_SORT,
            page: max(1, (int) ($input['page'] ?? 1)),
        );
    }

    /**
     * The filters as the URL carries them, for `useListQuery`.
     *
     * @return array{search: string, role: string, status: string, sort: string}
     */
    public function filters(): array
    {
        return [
            'search' => $this->search,
            'role' => $this->role->value ?? '',
            'status' => $this->deleted ? self::STATUS_DELETED : '',
            'sort' => $this->sort,
        ];
    }
}
