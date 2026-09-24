/* D4 — the name, and the kicker Manager alias small beside it. */
export function PlayerName({ name, alias }: { name: string; alias: string }) {
    return (
        <span className="flex flex-wrap items-baseline gap-x-2">
            <span className="font-medium text-brand-ink">{name}</span>
            <span className="text-xs text-brand-muted">{alias}</span>
        </span>
    );
}
