import { usePage } from '@inertiajs/react';
import { useCallback } from 'react';
import { translate, type Replacements } from '@/lib/i18n';

export type Translate = (key: string, replacements?: Replacements) => string;

/**
 * B6 — `t()` over the active locale's catalogue. Pass a literal key:
 * tests/Architecture/TranslationsTest.php can only check keys it can read.
 */
export function useTranslation(): {
    t: Translate;
    locale: string;
    locales: string[];
} {
    const { i18n, locale, locales } = usePage().props;

    const t = useCallback<Translate>(
        (key, replacements) => translate(i18n, key, replacements),
        [i18n],
    );

    return { t, locale, locales };
}
