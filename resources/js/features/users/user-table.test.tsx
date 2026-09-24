import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import type { UserRow } from './types';
import { UserTable } from './user-table';

const users: UserRow[] = [
    {
        id: 1,
        name: 'Anna',
        email: 'anna@example.com',
        role: 'admin',
        emailVerifiedAt: null,
        deleted: false,
    },
];

describe('B16 · sorting the user list from its headers', () => {
    it('sorts a column ascending first, then flips it', async () => {
        const user = userEvent.setup();
        const onSort = vi.fn();
        const { rerender } = render(
            <UserTable users={users} sort="name" onSort={onSort} />,
        );

        await user.click(screen.getByRole('button', { name: 'Email address' }));
        await user.click(screen.getByRole('button', { name: 'Name' }));

        rerender(<UserTable users={users} sort="-name" onSort={onSort} />);
        await user.click(screen.getByRole('button', { name: 'Name' }));

        expect(onSort.mock.calls).toEqual([['email'], ['-name'], ['name']]);
    });

    it('marks the sorted column for assistive technology', () => {
        render(<UserTable users={users} sort="-role" onSort={vi.fn()} />);

        expect(
            screen.getByRole('columnheader', { name: 'Role' }),
        ).toHaveAttribute('aria-sort', 'descending');
        expect(
            screen.getByRole('columnheader', { name: 'Name' }),
        ).toHaveAttribute('aria-sort', 'none');
    });
});
