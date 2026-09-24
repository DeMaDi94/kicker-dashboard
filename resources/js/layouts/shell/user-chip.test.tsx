import { render, screen } from '@testing-library/react';
import type { ReactNode } from 'react';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { PROFILE_MENU, UserChip } from './user-chip';

const page = vi.hoisted(() => ({
    avatar: undefined as string | undefined,
    guest: false,
}));

vi.mock('@inertiajs/react', () => ({
    usePage: () => ({
        props: {
            auth: {
                user: page.guest
                    ? null
                    : {
                          name: 'Ada Lovelace',
                          email: 'ada@example.com',
                          avatar: page.avatar,
                      },
            },
        },
    }),
    router: { flushAll: () => {} },
    Link: ({
        href,
        children,
        ...props
    }: {
        href: { url: string } | string;
        children: ReactNode;
    }) => (
        <a href={typeof href === 'string' ? href : href.url} {...props}>
            {children}
        </a>
    ),
}));

beforeEach(() => {
    page.avatar = undefined;
    page.guest = false;
});

describe('the user chip', () => {
    it('shows the initials and who is signed in', () => {
        render(<UserChip collapsed={false} />);

        const chip = screen.getByRole('button');

        expect(chip).toHaveTextContent('AL');
        expect(chip).toHaveTextContent('ada@example.com');
        expect(chip).toHaveAttribute('title', 'Signed in as Ada Lovelace');
    });

    it('shows the avatar instead of the initials when there is one', () => {
        page.avatar = 'https://example.test/ada.jpg';

        const { container } = render(<UserChip collapsed={false} />);

        expect(container.querySelector('img')).toHaveAttribute(
            'src',
            'https://example.test/ada.jpg',
        );
        expect(screen.getByRole('button')).not.toHaveTextContent('AL');
    });

    it('drops its two lines of text when the rail is folded', () => {
        render(<UserChip collapsed={true} />);

        expect(screen.getByText('Ada Lovelace').parentElement).toHaveClass(
            'compact:hidden',
        );
    });

    // ACC-01 — a guest reads the seasons; the chip is then the way in.
    it('offers a guest the login instead', () => {
        page.guest = true;

        render(<UserChip collapsed={false} />);

        expect(screen.getByRole('link', { name: 'Log in' })).toHaveAttribute(
            'href',
            '/login',
        );
        expect(screen.queryByRole('button')).toBeNull();
    });

    it('offers the account settings, then signing out', () => {
        expect(PROFILE_MENU.map((entry) => entry.title)).toEqual([
            'Settings',
            'Log out',
        ]);
        expect(PROFILE_MENU.at(-1)?.endsSession).toBe(true);
    });
});
