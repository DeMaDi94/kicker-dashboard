import type { SVGAttributes } from 'react';
import { cn } from '@/lib/utils';

/**
 * The mark: the primary-coloured tile the navigation rail opens with, and the
 * auth screens show above their form. A neutral placeholder — four pixel
 * blocks, one in the accent — drawn on a 36-unit grid so `size-9` lands every
 * edge on a whole pixel. A product replaces it (and public/favicon.svg) with
 * its own mark.
 */
export default function AppLogoIcon({
    className,
    ...props
}: SVGAttributes<SVGElement>) {
    return (
        <svg
            {...props}
            className={cn(
                'rounded-brand bg-brand-primary text-white',
                className,
            )}
            viewBox="0 0 36 36"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true"
        >
            <path
                fill="currentColor"
                d="M10 10h7v7h-7zM19 10h7v7h-7zM10 19h7v7h-7z"
            />
            <path
                className="fill-brand-accent dark:fill-brand-bg"
                d="M19 19h7v7h-7z"
            />
        </svg>
    );
}
