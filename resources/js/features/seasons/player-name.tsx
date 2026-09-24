/* D4 — the name, and the kicker Manager alias small beside it; on a phone
   under it, so every row keeps the same shape. */
export function PlayerName({ name, alias }: { name: string; alias: string }) {
    return (
        <span className="flex flex-col phone:flex-row phone:flex-wrap phone:items-baseline phone:gap-x-2">
            <span className="font-medium text-brand-ink">{name}</span>
            <span className="text-xs text-brand-muted">{alias}</span>
        </span>
    );
}
