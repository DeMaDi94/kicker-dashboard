import { X } from 'lucide-react';
import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

/*
 * The one surface the design has: a card on the page, held apart from it by
 * a single `--color-brand-line` hairline and nothing else — no shadow, no
 * second radius.
 *
 * `PanelHeader` is the title strip: the name at 15 px, whatever belongs beside
 * it (a badge, a refresh), and a fainter hairline under it than the one that
 * draws the card. Panels that are only a title and a list keep the title
 * inside the header and give the list its own rows; `PanelBody` is for
 * everything that needs the padding.
 */
export function Panel({
    className,
    children,
    ...props
}: React.ComponentProps<'section'>) {
    return (
        <section
            className={cn(
                'rounded-brand border border-brand-line bg-brand-card',
                className,
            )}
            {...props}
        >
            {children}
        </section>
    );
}

export function PanelHeader({
    title,
    badge,
    children,
}: {
    title: string;
    /** The Silkscreen chip beside the name. */
    badge?: string;
    children?: ReactNode;
}) {
    return (
        <div className="flex flex-wrap items-baseline gap-2 border-b border-brand-line-soft px-4 pt-3.5 pb-3">
            <h2 className="text-[15px] font-semibold text-brand-ink">
                {title}
            </h2>
            {badge && (
                <span className="rounded-brand bg-brand-accent-soft px-1.5 py-[3px] brand-label text-brand-accent-ink">
                    {badge}
                </span>
            )}
            {children && (
                <div className="ml-auto flex items-center gap-2">
                    {children}
                </div>
            )}
        </div>
    );
}

export function PanelBody({
    className,
    children,
    ...props
}: React.ComponentProps<'div'>) {
    return (
        <div className={cn('p-4', className)} {...props}>
            {children}
        </div>
    );
}

/*
 * The rule that opens a group of panels: the group's name in the Silkscreen
 * face, a hairline across the rest of the row and the count at the end.
 */
export function GroupRule({
    label,
    count,
}: {
    label: string;
    count?: ReactNode;
}) {
    return (
        <div className="mt-1 flex items-center gap-3">
            <span className="brand-label text-brand-primary">{label}</span>
            <div className="h-px flex-1 bg-brand-line" />
            {count !== undefined && (
                <span className="text-xs text-brand-faint">{count}</span>
            )}
        </div>
    );
}

/*
 * The design's add control: a dashed accent outline that goes solid on hover,
 * so „+ Row“ reads as an invitation rather than as one more button competing
 * with the row's own actions.
 */
export function AddButton({
    className,
    children,
    ...props
}: React.ComponentProps<'button'>) {
    return (
        <button
            type="button"
            className={cn(
                'rounded-brand border border-dashed border-brand-accent-line px-2.5 py-1.5 text-[13px] font-medium text-brand-accent-strong outline-none hover:border-solid hover:border-brand-accent hover:bg-brand-accent-wash focus-visible:shadow-focus',
                className,
            )}
            {...props}
        >
            {children}
        </button>
    );
}

/*
 * The row's own delete. It is quiet until it is pointed at and only then takes
 * the destructive colour, so a list of ten rows is not a list of ten red
 * buttons. It shows only an icon: the caller gives it its `aria-label`.
 */
export function DeleteButton({
    className,
    ...props
}: React.ComponentProps<'button'>) {
    return (
        <button
            type="button"
            className={cn(
                'flex size-7 flex-none items-center justify-center rounded-brand border border-transparent text-sm text-brand-faint outline-none hover:border-brand-danger-line hover:bg-brand-danger-soft hover:text-brand-danger focus-visible:shadow-focus',
                className,
            )}
            {...props}
        >
            <X className="size-4" aria-hidden />
        </button>
    );
}
