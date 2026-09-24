import type { SVGAttributes } from 'react';
import { cn } from '@/lib/utils';

/**
 * The mark (D9): a V for Vivalaraza drawn in two round strokes, the ball
 * resting in its fork, on the red tile the navigation rail opens with and the
 * auth screens show above their form. Drawn on a 36-unit grid;
 * public/favicon.svg is the same drawing in fixed colours. The ball keeps
 * the dark ground in both themes.
 */
export default function AppLogoIcon({
    className,
    ...props
}: SVGAttributes<SVGElement>) {
    return (
        <svg
            {...props}
            className={cn('rounded-mark bg-brand-accent', className)}
            viewBox="0 0 36 36"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true"
        >
            <path
                d="M9.5 9.5 18 27l8.5-17.5"
                fill="none"
                strokeWidth="4.2"
                strokeLinecap="round"
                strokeLinejoin="round"
                className="stroke-white"
            />
            <circle
                cx="18"
                cy="12.5"
                r="3.4"
                className="fill-brand-primary dark:fill-brand-bg"
            />
        </svg>
    );
}
