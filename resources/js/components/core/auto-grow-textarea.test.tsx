import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { useState } from 'react';
import { beforeAll, describe, expect, it } from 'vitest';
import { AutoGrowTextarea } from './auto-grow-textarea';

/*
 * jsdom lays nothing out, so scrollHeight is always 0. Standing in for it
 * with the line count is enough to prove the rule: the field's height
 * follows its content instead of staying at one row.
 */
beforeAll(() => {
    Object.defineProperty(HTMLTextAreaElement.prototype, 'scrollHeight', {
        configurable: true,
        get(this: HTMLTextAreaElement) {
            return 24 * Math.max(1, this.value.split('\n').length);
        },
    });
});

function Harness() {
    const [value, setValue] = useState('');

    return (
        <AutoGrowTextarea
            aria-label="Title"
            value={value}
            onChange={(event) => setValue(event.target.value)}
        />
    );
}

describe('a field that grows with its content', () => {
    it('grows as lines are added and shrinks again', async () => {
        const user = userEvent.setup();
        render(<Harness />);

        const field = screen.getByLabelText('Title');
        expect(field.style.height).toBe('24px');

        await user.type(field, 'First line{Enter}Second line');
        expect(field.style.height).toBe('48px');

        await user.clear(field);
        expect(field.style.height).toBe('24px');
    });

    it('never shows its own scrollbar', () => {
        render(<Harness />);

        expect(screen.getByLabelText('Title')).toHaveClass('overflow-hidden');
    });
});
