import { router } from '@inertiajs/react';
import type { HTMLAttributes } from 'react';
import { useTranslation } from '@/hooks/use-translation';
import { cn } from '@/lib/utils';

const ONE_YEAR_IN_SECONDS = 60 * 60 * 24 * 365;

/**
 * Each language is named in itself („Deutsch“, „English“), so a reader who
 * cannot read the current one still finds their own.
 */
const endonym = (locale: string): string =>
    new Intl.DisplayNames([locale], { type: 'language' }).of(locale) ?? locale;

/*
 * B6 — the interface language, per browser. The choice goes into the
 * unencrypted `locale` cookie that HandleLocale reads; the reload then brings
 * the new catalogue, whose `i18n` key has changed with the locale.
 */
function switchTo(locale: string): void {
    document.cookie = `locale=${locale}; path=/; max-age=${ONE_YEAR_IN_SECONDS}; samesite=lax`;
    document.documentElement.lang = locale;
    router.reload();
}

export default function LocaleTabs({
    className = '',
    ...props
}: HTMLAttributes<HTMLDivElement>) {
    const { t, locale, locales } = useTranslation();

    return (
        <div
            role="group"
            aria-label={t('Language')}
            className={cn(
                'inline-flex gap-1 rounded-brand border border-brand-line bg-brand-rail p-1',
                className,
            )}
            {...props}
        >
            {locales.map((each) => (
                <button
                    key={each}
                    type="button"
                    lang={each}
                    aria-pressed={each === locale}
                    onClick={() => switchTo(each)}
                    className={cn(
                        'flex items-center rounded-brand border px-3.5 py-1.5 text-sm transition-colors outline-none focus-visible:shadow-focus',
                        each === locale
                            ? 'border-brand-line bg-brand-card font-medium text-brand-ink'
                            : 'border-transparent text-brand-muted hover:bg-brand-hover hover:text-brand-ink',
                    )}
                >
                    {endonym(each)}
                </button>
            ))}
        </div>
    );
}
