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
        <fieldset className="grid gap-2" disabled={disabled}>
            <legend className="mb-2 text-sm font-medium">{t('Players')}</legend>

            {players.length === 0 && (
                <p className="text-sm text-brand-muted">
                    {t('No players yet. Create them first.')}
                </p>
            )}

            {players.map((player) => (
                <div key={player.id} className="flex items-center gap-2">
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
                    <Label htmlFor={`player-${player.id}`}>
                        <PlayerName name={player.name} alias={player.alias} />
                    </Label>
                </div>
            ))}

            <InputError message={error} />
        </fieldset>
    );
}
