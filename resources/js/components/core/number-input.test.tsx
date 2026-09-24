import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { useState } from 'react';
import { describe, expect, it } from 'vitest';
import { NumberInput } from './number-input';

/* The test setup's useTranslation() answers with the `en` locale. */

function Harness({
    decimals,
    allowNegative,
    initial = null,
}: {
    decimals?: number;
    allowNegative?: boolean;
    initial?: number | null;
}) {
    const [value, setValue] = useState<number | null>(initial);

    return (
        <>
            <NumberInput
                aria-label="Number"
                value={value}
                decimals={decimals}
                allowNegative={allowNegative}
                onValueChange={setValue}
            />
            <output>{String(value)}</output>
            <button type="button" onClick={() => setValue(42.5)}>
                set elsewhere
            </button>
        </>
    );
}

describe('negative entry', () => {
    it('clamps to 0 instead of rejecting the keystroke', async () => {
        const user = userEvent.setup();
        render(<Harness />);

        const field = screen.getByLabelText('Number');
        await user.type(field, '-5');

        expect(field).toHaveValue('0');
        expect(screen.getByRole('status').textContent).toBe('0');
    });

    it('keeps the sign where the field allows it', async () => {
        const user = userEvent.setup();
        render(<Harness allowNegative />);

        const field = screen.getByLabelText('Number');
        await user.type(field, '-5');

        expect(field).toHaveValue('-5');
        expect(screen.getByRole('status').textContent).toBe('-5');
    });
});

describe('the active locale’s notation in a field', () => {
    it('reads a number and drops unnecessary decimals on blur', async () => {
        const user = userEvent.setup();
        render(<Harness />);

        const field = screen.getByLabelText('Number');
        await user.type(field, '1,234.50');
        await user.tab();

        expect(screen.getByRole('status').textContent).toBe('1234.5');
        expect(field).toHaveValue('1,234.5');
    });

    it('keeps fixed decimals where the field asks for them', async () => {
        const user = userEvent.setup();
        render(<Harness decimals={2} />);

        const field = screen.getByLabelText('Number');
        await user.type(field, '0.8');
        await user.tab();

        expect(field).toHaveValue('0.80');
    });

    it('is empty and null when it holds no digit', async () => {
        const user = userEvent.setup();
        render(<Harness initial={3} />);

        const field = screen.getByLabelText('Number');
        expect(field).toHaveValue('3');

        await user.clear(field);
        await user.tab();

        expect(field).toHaveValue('');
        expect(screen.getByRole('status').textContent).toBe('null');
    });

    it('shows a value changed elsewhere', async () => {
        const user = userEvent.setup();
        render(<Harness />);

        await user.click(screen.getByRole('button', { name: 'set elsewhere' }));

        expect(screen.getByLabelText('Number')).toHaveValue('42.5');
    });
});
