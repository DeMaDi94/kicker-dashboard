import { Share2 } from 'lucide-react';
import { toast } from '@/components/core/toast';
import { Button } from '@/components/ui/button';
import { useClipboard } from '@/hooks/use-clipboard';
import type { Translate } from '@/hooks/use-translation';
import { useTranslation } from '@/hooks/use-translation';
import { formatCents } from '@/lib/money';
import { formatNumber } from '@/lib/number';
import type { MatchdayBlock } from './types';

/*
 * MD-06 — a complete matchday as text: the heading, a line per player in the
 * matchday's order, the league average and the money into the box, then the
 * link. D18 — the short names without the alias. Null until the matchday is
 * complete (MD-02).
 */
export function matchdayShareText({
    app,
    seasonName,
    matchday,
    url,
    t,
    locale,
}: {
    app: string;
    seasonName: string;
    matchday: MatchdayBlock;
    url: string;
    t: Translate;
    locale: string;
}): string | null {
    const highlights = matchday.highlights;

    if (!matchday.complete || highlights === null) {
        return null;
    }

    return [
        t(':app :season · Matchday :number', {
            app,
            season: seasonName,
            number: matchday.number,
        }),
        '',
        ...matchday.rows.map(
            (row) =>
                `${row.place}. ${row.name} ${row.points} · ${formatCents(row.penaltyCents ?? 0, locale)}`,
        ),
        '',
        t('Ø :average points · :amount into the box', {
            average: formatNumber(highlights.average, locale, 1),
            amount: formatCents(highlights.penaltyCents, locale),
        }),
        '',
        url,
    ].join('\n');
}

/*
 * MD-06, D18 — the device's share menu; without one, or when it fails, the
 * clipboard. Closing the menu is no failure.
 */
export function ShareMatchdayButton({ text }: { text: string }) {
    const { t } = useTranslation();
    const [, copy] = useClipboard();

    const share = async () => {
        if (typeof navigator.share === 'function') {
            try {
                await navigator.share({ text });

                return;
            } catch (error) {
                if (
                    error instanceof DOMException &&
                    error.name === 'AbortError'
                ) {
                    return;
                }
            }
        }

        if (await copy(text)) {
            toast(t('Copied'), 'ok');
        }
    };

    return (
        <Button size="sm" variant="outline" onClick={share}>
            <Share2 />
            <span className="max-phone:sr-only">{t('Share')}</span>
        </Button>
    );
}
