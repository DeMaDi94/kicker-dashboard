import { render, screen } from '@testing-library/react';
import type { ReactNode } from 'react';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import type { TabGroup } from '@/types';
import { TabBar } from './tab-bar';

const currentUrl = vi.hoisted(() => ({ value: '/projects/7' }));

vi.mock('@inertiajs/react', () => ({
    usePage: () => ({ url: currentUrl.value }),
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

const PROJECT_TABS: TabGroup = {
    label: 'Project',
    items: [
        { title: 'Overview', href: '/projects/7' },
        {
            title: 'Planning',
            href: '/projects/7/tasks',
            covers: ['/projects/7/milestones'],
        },
        { title: 'Billing', href: '/projects/7/billing' },
        { title: 'Reports' },
    ],
};

function currentTitles(): string[] {
    return screen
        .getAllByRole('link')
        .filter((link) => link.getAttribute('aria-current') === 'page')
        .map((link) => link.textContent ?? '');
}

beforeEach(() => {
    currentUrl.value = '/projects/7';
});

describe('the tab bar', () => {
    it('shows its tabs in order, named by its label', () => {
        render(<TabBar {...PROJECT_TABS} />);

        expect(
            screen.getByRole('navigation', { name: 'Project' }).textContent,
        ).toBe('OverviewPlanningBillingReports');
    });

    it.each([
        ['/projects/7', 'Overview'],
        ['/projects/7/tasks', 'Planning'],
        ['/projects/7/milestones', 'Planning'],
        ['/projects/7/billing', 'Billing'],
        ['/projects/7/billing/invoices/3', 'Billing'],
    ])('on %s marks only %s', (path, title) => {
        currentUrl.value = path;

        render(<TabBar {...PROJECT_TABS} />);

        expect(currentTitles()).toEqual([title]);
    });

    it('matches whole path segments, not a string prefix', () => {
        currentUrl.value = '/projects/70';

        render(<TabBar {...PROJECT_TABS} />);

        expect(currentTitles()).toEqual([]);
    });

    it('shows a tab without a screen yet, but not as a link', () => {
        render(<TabBar {...PROJECT_TABS} />);

        expect(screen.getByText('Reports')).toHaveAttribute(
            'aria-disabled',
            'true',
        );
        expect(screen.queryByRole('link', { name: 'Reports' })).toBeNull();
    });
});
