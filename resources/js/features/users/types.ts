/** B13 — the stored role values, mirroring App\Domain\Users\Role. */
export type Role = 'admin' | 'user';

/** Mirrors App\Http\Users\Shared\UserRow. */
export type UserRow = {
    id: number;
    name: string;
    email: string;
    role: Role | null;
    emailVerifiedAt: string | null;
    deleted: boolean;
};

/** The user list's URL state (B16). `sort` is a column key, `-` for descending. */
export type UserListFilters = {
    search: string;
    role: string;
    status: string;
    sort: string;
};
