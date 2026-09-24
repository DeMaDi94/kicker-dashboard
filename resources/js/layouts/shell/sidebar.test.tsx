import { render, screen } from '@testing-library/react';
import { LayoutGrid, Settings } from 'lucide-react';
import type { ReactNode } from 'react';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import type { ShellNavItem } from './nav-items';
import { AppSidebar } from './sidebar';

const page = vi.hoisted(() => ({ url: '/dashboard' }));

vi.mock('@inertiajs/react', () => ({
    usePage: () => ({
        url: page.url,
        props: {
            name: 'Blueprint',
            auth: { user: { name: 'Ada Lovelace', email: 'ada@example.com' } },
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

const ITEMS: ShellNavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
        sections: ['/dashboard', '/projects'],
    },
    {
        title: 'Settings',
        href: '/settings/profile',
        icon: Settings,
        sections: ['/settings'],
    },
];

const entries = () =>
    screen.getAllByRole('link').filter((link) => link.hasAttribute('title'));

beforeEach(() => {
    page.url = '/dashboard';
});

describe('the navigation rail', () => {
    it('lists its entries in order', () => {
        render(<AppSidebar collapsed={false} items={ITEMS} />);

        expect(entries().map((link) => link.textContent)).toEqual([
            'Dashboard',
            'Settings',
        ]);
    });

    it('opens with the app name beside the mark', () => {
        render(<AppSidebar collapsed={false} items={ITEMS} />);

        expect(screen.getByText('Blueprint')).toBeInTheDocument();
    });

    it('marks the entry the current page belongs to, across its section', () => {
        page.url = '/settings/appearance';

        render(<AppSidebar collapsed={false} items={ITEMS} />);

        expect(screen.getByRole('link', { name: 'Settings' })).toHaveAttribute(
            'aria-current',
            'page',
        );
        expect(
            screen.getByRole('link', { name: 'Dashboard' }),
        ).not.toHaveAttribute('aria-current');
    });

    it.each(['/projects/7', '/projects/7/tasks'])(
        'keeps an entry marked on every path of its sections (%s)',
        (url) => {
            page.url = url;

            render(<AppSidebar collapsed={false} items={ITEMS} />);

            expect(
                entries()
                    .filter(
                        (link) => link.getAttribute('aria-current') === 'page',
                    )
                    .map((link) => link.textContent),
            ).toEqual(['Dashboard']);
        },
    );

    it('matches whole path segments, not a string prefix', () => {
        page.url = '/settingsx';

        render(<AppSidebar collapsed={false} items={ITEMS} />);

        expect(
            screen.getByRole('link', { name: 'Settings' }),
        ).not.toHaveAttribute('aria-current');
    });

    /* Folding must not move what stays visible: the entries keep their own
       box exactly as it was and only the labels go, so the icon column does
       not slide. */
    it("leaves each entry's own layout untouched when it folds", () => {
        const { unmount } = render(
            <AppSidebar collapsed={false} items={ITEMS} />,
        );
        const open = entries().map((link) => link.className);

        unmount();
        render(<AppSidebar collapsed={true} items={ITEMS} />);

        expect(entries().map((link) => link.className)).toEqual(open);
    });

    it('hides the entry labels once it is folded', () => {
        const { unmount } = render(
            <AppSidebar collapsed={false} items={ITEMS} />,
        );
        expect(screen.getByText('Dashboard')).not.toHaveClass('compact:hidden');

        unmount();
        render(<AppSidebar collapsed={true} items={ITEMS} />);

        expect(screen.getByText('Dashboard')).toHaveClass('compact:hidden');
    });

    /* Below the phone breakpoint the bar shows the icons alone, but each
       entry keeps its label as its accessible name rather than losing it
       with `display: none`. */
    it('keeps every entry named when the bar shows icons only', () => {
        render(<AppSidebar collapsed={false} items={ITEMS} />);

        for (const title of ['Dashboard', 'Settings']) {
            expect(screen.getByText(title)).toHaveClass('max-phone:sr-only');
            expect(screen.getByRole('link', { name: title })).toHaveAttribute(
                'title',
                title,
            );
        }
    });
});
