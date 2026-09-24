import type { InertiaLinkProps } from '@inertiajs/react';
import { router } from '@inertiajs/react';
import type { MouseEvent } from 'react';

export const ROW_LINK = 'cursor-pointer hover:bg-brand-hover';

/*
 * A click anywhere on a player's row opens what the name links to. The name
 * stays the real link, so keyboard and screen reader use are unchanged; a
 * click on the link itself, or one that ends a text selection, is left alone.
 */
export function visitRow(href: NonNullable<InertiaLinkProps['href']>) {
    return (event: MouseEvent<HTMLElement>) => {
        if (
            (event.target instanceof Element && event.target.closest('a')) ||
            window.getSelection()?.toString()
        ) {
            return;
        }

        router.visit(href);
    };
}
