import type { ReactNode } from 'react';

/*
 * The title block every screen opens with: the view name, an optional
 * sentence under it saying what the screen is for, and the room to the right
 * that a save state and the screen's primary action sit in.
 *
 * The breadcrumb trail already names the view; this is the heading a reader
 * — and every `getByRole('heading')` in the suite — actually lands on.
 */
export function PageTitle({
    title,
    description,
    children,
}: {
    title: string;
    description?: string;
    children?: ReactNode;
}) {
    return (
        <div className="mb-4.5 flex flex-wrap items-start justify-between gap-x-4 gap-y-1.5">
            <div className="flex flex-col gap-1.5">
                <h1 className="text-[21px] leading-tight font-semibold tracking-[-0.02em] text-brand-ink">
                    {title}
                </h1>
                {description && (
                    <p className="text-[13px] text-brand-muted">
                        {description}
                    </p>
                )}
            </div>
            {children}
        </div>
    );
}
