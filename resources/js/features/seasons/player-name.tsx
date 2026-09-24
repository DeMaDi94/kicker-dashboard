import type { InertiaLinkProps } from '@inertiajs/react';
import { Link } from '@inertiajs/react';

/* D4 — the name, and the kicker Manager alias small beside it; on a phone
   under it, so every row keeps the same shape. STAT-01 — with an `href`, the
   name leads to the player's own page. */
export function PlayerName({
    name,
    alias,
    href,
}: {
    name: string;
    alias: string;
    href?: NonNullable<InertiaLinkProps['href']>;
}) {
    return (
        <span className="flex flex-col phone:flex-row phone:flex-wrap phone:items-baseline phone:gap-x-2">
            {href === undefined ? (
                <span className="font-medium text-brand-ink">{name}</span>
            ) : (
                <Link
                    href={href}
                    className="rounded-brand font-medium text-brand-ink underline-offset-2 outline-none hover:underline focus-visible:shadow-focus"
                >
                    {name}
                </Link>
            )}
            <span className="text-xs text-brand-muted">{alias}</span>
        </span>
    );
}
