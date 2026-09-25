import type { Translate } from '@/hooks/use-translation';
import type { PublicPage } from './types';

/** VIS-01 — a public page's name; the stored value is never shown. */
export function pageLabel(page: PublicPage, t: Translate): string {
    switch (page) {
        case 'season':
            return t('Season view');
        case 'player':
            return t('Players');
        case 'compare':
            return t('Head-to-head');
        case 'records':
            return t('League records');
    }
}
