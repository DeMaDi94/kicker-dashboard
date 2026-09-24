import InputError from '@/components/input-error';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useTranslation } from '@/hooks/use-translation';

/* A select cannot hold an empty value; this stands for "no settlement". */
const NONE = 'none';

/*
 * PEN-04 — the matchday the penalty box is settled after, or none.
 */
export function SettlementSelect({
    matchdays,
    value,
    onChange,
    error,
}: {
    matchdays: number[];
    value: number | null;
    onChange: (value: number | null) => void;
    error?: string;
}) {
    const { t } = useTranslation();

    return (
        <div className="grid gap-2">
            <Label htmlFor="settlement_matchday">
                {t('Interim settlement after')}
            </Label>
            <Select
                value={value === null ? NONE : String(value)}
                onValueChange={(next) =>
                    onChange(next === NONE ? null : Number(next))
                }
            >
                <SelectTrigger id="settlement_matchday" className="w-full">
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value={NONE}>
                        {t('No interim settlement')}
                    </SelectItem>
                    {matchdays.map((each) => (
                        <SelectItem key={each} value={String(each)}>
                            {t('Matchday :number', { number: each })}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
            <p className="text-[13px] text-brand-muted">
                {t(
                    'The penalties are settled once after this matchday. Points and places carry on.',
                )}
            </p>
            <InputError message={error} />
        </div>
    );
}
