import InputError from '@/components/input-error';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { useTranslation } from '@/hooks/use-translation';
import { PlayerName } from './player-name';
import type { PlayerOption } from './types';

/* SEA-02 — the players who take part in a season. */
export function PlayerChecklist({
    players,
    selected,
    onChange,
    disabled = false,
    error,
}: {
    players: PlayerOption[];
    selected: number[];
    onChange: (selected: number[]) => void;
    disabled?: boolean;
    error?: string;
}) {
    const { t } = useTranslation();

    return (
        <fieldset className="grid" disabled={disabled}>
            <legend className="mb-2 text-sm font-medium">{t('Players')}</legend>

            {players.length === 0 && (
                <p className="text-sm text-brand-muted">
                    {t('No players yet. Create them first.')}
                </p>
            )}

            {/* Each row is a 44 px tap target, the whole row toggling the box. */}
            {players.map((player) => (
                <div
                    key={player.id}
                    className="flex min-h-11 items-center gap-3 rounded-brand px-2 hover:bg-brand-hover"
                >
                    <Checkbox
                        id={`player-${player.id}`}
                        checked={selected.includes(player.id)}
                        disabled={disabled}
                        onCheckedChange={(checked) =>
                            onChange(
                                checked === true
                                    ? [...selected, player.id]
                                    : selected.filter((id) => id !== player.id),
                            )
                        }
                    />
                    <Label
                        htmlFor={`player-${player.id}`}
                        className="flex flex-1 cursor-pointer items-center self-stretch py-1.5"
                    >
                        <PlayerName name={player.name} alias={player.alias} />
                    </Label>
                </div>
            ))}

            <InputError message={error} />
        </fieldset>
    );
}
