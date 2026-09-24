import { Link } from '@inertiajs/react';
import AppLogoIcon from '@/components/app-logo-icon';
import LocaleTabs from '@/components/locale-tabs';
import { useTranslation } from '@/hooks/use-translation';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

/*
 * The signed-out screens: the mark, the page's title and one sentence under
 * it, the form on a hairline-bordered surface, and the language switch — a
 * visitor who cannot read the default language has to be able to change it
 * before signing in.
 */
export default function AuthSimpleLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    const { t } = useTranslation();

    return (
        <div className="flex min-h-svh flex-col items-center justify-center gap-6 bg-brand-rail p-6 text-brand-ink md:p-10">
            <div className="w-full max-w-sm">
                <div className="flex flex-col gap-6">
                    <div className="flex flex-col items-center gap-4">
                        <Link
                            href={home()}
                            className="rounded-brand outline-none focus-visible:shadow-focus"
                        >
                            <AppLogoIcon className="size-9" />
                            <span className="sr-only">{t(title ?? '')}</span>
                        </Link>

                        <div className="flex flex-col gap-1.5 text-center">
                            <h1 className="text-[21px] leading-tight font-semibold tracking-[-0.02em]">
                                {t(title ?? '')}
                            </h1>
                            <p className="text-[13px] text-brand-muted">
                                {t(description ?? '')}
                            </p>
                        </div>
                    </div>

                    <div className="rounded-brand border border-brand-line bg-brand-card p-6">
                        {children}
                    </div>

                    <LocaleTabs className="self-center" />
                </div>
            </div>
        </div>
    );
}
