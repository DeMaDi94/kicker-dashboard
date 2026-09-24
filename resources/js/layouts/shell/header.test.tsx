import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import type { ReactNode } from 'react';
import { describe, expect, it, vi } from 'vitest';
import { translate } from '@/lib/i18n';
import { AppHeader } from './header';

vi.mock('@/hooks/use-translation', () => ({
    useTranslation: () => ({
        t: (key: string) => translate({ Points: 'Punkte' }, key),
        locale: 'de',
        locales: ['de'],
    }),
}));

vi.mock('@inertiajs/react', () => ({
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

const TRAIL = [
    { title: 'Settings', href: '/settings/profile' },
    { title: 'Appearance', href: '/settings/appearance' },
];

describe('the header', () => {
    const renderHeader = (collapsed: boolean, onToggleNav = () => {}) =>
        render(
            <AppHeader
                breadcrumbs={TRAIL}
                collapsed={collapsed}
                onToggleNav={onToggleNav}
            />,
        );

    it('has a fold control that says which way it goes', () => {
        const { unmount } = renderHeader(false);

        expect(
            screen.getByRole('button', { name: 'Collapse sidebar' }),
        ).toHaveAttribute('aria-expanded', 'true');

        unmount();
        renderHeader(true);

        expect(
            screen.getByRole('button', { name: 'Expand sidebar' }),
        ).toHaveAttribute('aria-expanded', 'false');
    });

    it('asks for the rail to be flipped', async () => {
        const user = userEvent.setup();
        const onToggleNav = vi.fn();

        renderHeader(false, onToggleNav);
        await user.click(
            screen.getByRole('button', { name: 'Collapse sidebar' }),
        );

        expect(onToggleNav).toHaveBeenCalledOnce();
    });

    it('shows the trail, linking every crumb but the current view', () => {
        renderHeader(false);

        const trail = screen.getByRole('navigation', { name: 'Breadcrumb' });

        expect(trail.textContent).toBe('Settings/Appearance');
        expect(screen.getByRole('link', { name: 'Settings' })).toHaveAttribute(
            'href',
            '/settings/profile',
        );
        expect(screen.queryByRole('link', { name: 'Appearance' })).toBeNull();
        expect(screen.getByText('Appearance')).toHaveAttribute(
            'aria-current',
            'page',
        );
    });

    it('shows a record’s own name as it is, not as a translation key', () => {
        render(
            <AppHeader
                breadcrumbs={[
                    { title: 'Points', href: '/points' },
                    { title: 'Points', href: '/players/1', verbatim: true },
                ]}
                collapsed={false}
                onToggleNav={() => {}}
            />,
        );

        expect(
            screen.getByRole('navigation', { name: 'Breadcrumb' }).textContent,
        ).toBe('Punkte/Points');
    });
});
