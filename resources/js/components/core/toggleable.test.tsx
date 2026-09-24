import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import { activatableProps } from './toggleable';

describe('non-native controls', () => {
    it('is focusable and announced as what it behaves like', () => {
        render(
            <span
                {...activatableProps('checkbox', () => {}, { checked: true })}
            >
                Optional
            </span>,
        );

        const control = screen.getByRole('checkbox', { name: 'Optional' });

        expect(control).toHaveAttribute('tabindex', '0');
        expect(control).toBeChecked();
    });

    it('activates on Enter, on Space and on a click', async () => {
        const activate = vi.fn();
        const user = userEvent.setup();

        render(<span {...activatableProps('button', activate)}>Remove</span>);

        const control = screen.getByRole('button', { name: 'Remove' });

        await user.click(control);
        control.focus();
        await user.keyboard('{Enter}');
        await user.keyboard(' ');

        expect(activate).toHaveBeenCalledTimes(3);
    });

    it('is skipped by the keyboard and inert while disabled', async () => {
        const activate = vi.fn();
        const user = userEvent.setup();

        render(
            <span {...activatableProps('link', activate, { disabled: true })}>
                Open
            </span>,
        );

        const control = screen.getByRole('link', { name: 'Open' });

        expect(control).toHaveAttribute('tabindex', '-1');
        expect(control).toHaveAttribute('aria-disabled', 'true');

        await user.click(control);

        expect(activate).not.toHaveBeenCalled();
    });
});
