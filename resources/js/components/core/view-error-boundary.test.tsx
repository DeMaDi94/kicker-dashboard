import { render, screen } from '@testing-library/react';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { ViewErrorBoundary } from './view-error-boundary';

const toasted = vi.hoisted(() => vi.fn());

vi.mock('sonner', () => ({ toast: toasted }));

function Broken(): never {
    throw new Error('Prices could not be read');
}

beforeEach(() => {
    toasted.mockReset();
    // React logs the caught error itself; the test asserts on the toast.
    vi.spyOn(console, 'error').mockImplementation(() => {});
});

afterEach(() => {
    vi.restoreAllMocks();
});

describe('a view that fails to render', () => {
    it('keeps the page alive and reports the failure as a 9 s toast', () => {
        render(
            <>
                <nav>Navigation</nav>
                <ViewErrorBoundary view="Reports">
                    <Broken />
                </ViewErrorBoundary>
            </>,
        );

        expect(screen.getByText('Navigation')).toBeInTheDocument();
        expect(
            screen.getByText('This view could not be loaded.'),
        ).toBeInTheDocument();

        expect(toasted).toHaveBeenCalledWith(
            '⚠ Could not load the view „Reports“: Prices could not be read',
            expect.objectContaining({ duration: 9000 }),
        );
    });

    it('renders the next view instead of staying dead', () => {
        const { rerender } = render(
            <ViewErrorBoundary view="Reports">
                <Broken />
            </ViewErrorBoundary>,
        );

        rerender(
            <ViewErrorBoundary view="Schedule">
                <p>Schedule</p>
            </ViewErrorBoundary>,
        );

        expect(screen.getByText('Schedule')).toBeInTheDocument();
    });
});

describe('the failure a view says in its own words', () => {
    it('hands the message to the view’s own fallback', () => {
        render(
            <ViewErrorBoundary
                view="Dashboard"
                fallback={(message) => (
                    <p>{`Could not load (${message}). Please reload.`}</p>
                )}
            >
                <Broken />
            </ViewErrorBoundary>,
        );

        expect(
            screen.getByText(
                'Could not load (Prices could not be read). Please reload.',
            ),
        ).toBeInTheDocument();
    });
});
