import { useTranslation } from '@/hooks/use-translation';
import { cn } from '@/lib/utils';
import type { FormGrade } from './types';

/*
 * STAT-07 — the day's place of the last five matchdays, each graded by the
 * third of the places it falls in. The grade is spelled out for screen
 * readers and in the title, never colour alone.
 */
export function FormStrip({
    form,
}: {
    form: { matchday: number; place: number; grade: FormGrade }[];
}) {
    const { t } = useTranslation();
    const label = (grade: FormGrade) =>
        grade === 'good' ? t('Good') : grade === 'bad' ? t('Bad') : t('Middle');

    if (form.length === 0) {
        return <p className="text-sm text-brand-muted">–</p>;
    }

    return (
        <ol className="flex gap-1.5">
            {form.map((day) => (
                <li
                    key={day.matchday}
                    title={`${t('Matchday :number', { number: day.matchday })} · ${label(day.grade)}`}
                    className={cn(
                        'flex size-10 flex-col items-center justify-center rounded-brand border brand-figure text-sm font-semibold',
                        day.grade === 'good' &&
                            'border-brand-success/40 bg-brand-success-soft text-brand-success',
                        day.grade === 'middle' &&
                            'border-brand-line bg-brand-row-alt text-brand-ink-soft',
                        day.grade === 'bad' &&
                            'border-brand-danger-line bg-brand-danger-soft text-brand-danger',
                    )}
                >
                    {day.place}.
                    <span className="text-[9px] font-normal text-brand-muted">
                        {day.matchday}
                    </span>
                    <span className="sr-only">{label(day.grade)}</span>
                </li>
            ))}
        </ol>
    );
}
