import { useLayoutEffect, useRef, type ComponentProps } from 'react';
import { cn } from '@/lib/utils';

/*
 * A multi-line field that grows with its content, so the whole text stays
 * visible without a scrollbar: the height is reset and then set to the
 * content's scroll height.
 */

type AutoGrowTextareaProps = ComponentProps<'textarea'>;

export function AutoGrowTextarea({
    className,
    value,
    onChange,
    ...props
}: AutoGrowTextareaProps) {
    const ref = useRef<HTMLTextAreaElement>(null);

    const grow = () => {
        const el = ref.current;

        if (!el) {
            return;
        }

        el.style.height = 'auto';
        el.style.height = `${el.scrollHeight}px`;
    };

    /* Before paint, so a long value never shows a scrollbar first. */
    useLayoutEffect(grow, [value]);

    return (
        <textarea
            {...props}
            ref={ref}
            value={value}
            rows={props.rows ?? 1}
            onChange={(event) => {
                grow();
                onChange?.(event);
            }}
            className={cn(
                'w-full resize-none overflow-hidden rounded-brand border border-brand-line bg-brand-card px-3 py-2 outline-none focus-visible:shadow-focus',
                className,
            )}
        />
    );
}
