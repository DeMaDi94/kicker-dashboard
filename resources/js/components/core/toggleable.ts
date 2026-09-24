import type { KeyboardEvent } from 'react';

/*
 * Anything that behaves like a button, a link or a checkbox without being one
 * is focusable, is announced as what it behaves like, and responds to Enter
 * and Space. Rather than repeat the four attributes at every call site, they
 * come from here.
 */

type ActivatableProps = {
    role: 'button' | 'link' | 'checkbox';
    tabIndex: number;
    onKeyDown: (event: KeyboardEvent<HTMLElement>) => void;
    onClick: () => void;
    'aria-checked'?: boolean;
    'aria-disabled'?: boolean;
};

export function activatableProps(
    role: ActivatableProps['role'],
    onActivate: () => void,
    options: { checked?: boolean; disabled?: boolean } = {},
): ActivatableProps {
    const activate = () => {
        if (options.disabled) {
            return;
        }

        onActivate();
    };

    return {
        role,
        tabIndex: options.disabled ? -1 : 0,
        onClick: activate,
        onKeyDown: (event) => {
            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }

            // Space would scroll the page, Enter would submit a surrounding
            // form — neither is what a fake checkbox means.
            event.preventDefault();
            activate();
        },
        ...(role === 'checkbox' ? { 'aria-checked': !!options.checked } : {}),
        ...(options.disabled ? { 'aria-disabled': true } : {}),
    };
}
