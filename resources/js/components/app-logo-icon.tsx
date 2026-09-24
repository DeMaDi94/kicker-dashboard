import { useId, type SVGAttributes } from 'react';
import { cn } from '@/lib/utils';

/**
 * The mark (D9): a football on the move — a ball with a red centre patch and
 * three red speed lines — on the primary tile the navigation rail opens with
 * and the auth screens show above their form. Drawn on a 36-unit grid so
 * `size-9` lands on whole pixels; public/favicon.svg is the same drawing in
 * fixed light colours. In dark mode the tile turns red, so the lines go
 * white and the patches take the page colour.
 */
export default function AppLogoIcon({
    className,
    ...props
}: SVGAttributes<SVGElement>) {
    const clip = useId();

    return (
        <svg
            {...props}
            className={cn('rounded-brand bg-brand-primary', className)}
            viewBox="0 0 36 36"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true"
        >
            <defs>
                <clipPath id={clip}>
                    <circle cx="21.5" cy="18" r="10" />
                </clipPath>
            </defs>
            <path
                d="M3.5 12.5h4M2 18h5M3.5 23.5h4"
                strokeWidth="2.2"
                strokeLinecap="round"
                className="stroke-brand-accent dark:stroke-white"
            />
            <circle cx="21.5" cy="18" r="10" className="fill-white" />
            <g clipPath={`url(#${clip})`}>
                <g className="fill-brand-primary dark:fill-brand-bg">
                    <polygon points="21.5,11.6 18.74,9.6 19.8,6.35 23.2,6.35 24.26,9.6" />
                    <polygon points="27.59,16.02 28.64,12.78 32.05,12.78 33.1,16.02 30.34,18.03" />
                    <polygon points="25.26,23.18 28.67,23.18 29.72,26.42 26.97,28.42 24.21,26.42" />
                    <polygon points="17.74,23.18 18.79,26.42 16.03,28.42 13.28,26.42 14.33,23.18" />
                    <polygon points="15.41,16.02 12.66,18.03 9.9,16.02 10.95,12.78 14.36,12.78" />
                </g>
                <path
                    d="M21.5 14.7L21.5 11.6M24.26 9.6L28.64 12.78M24.64 16.98L27.59 16.02M30.34 18.03L28.67 23.18M23.44 20.67L25.26 23.18M24.21 26.42L18.79 26.42M19.56 20.67L17.74 23.18M14.33 23.18L12.66 18.03M18.36 16.98L15.41 16.02M14.36 12.78L18.74 9.6"
                    strokeWidth="1"
                    strokeLinecap="round"
                    className="stroke-brand-primary dark:stroke-brand-bg"
                />
            </g>
            <polygon
                points="21.5,14.7 24.64,16.98 23.44,20.67 19.56,20.67 18.36,16.98"
                className="fill-brand-accent dark:fill-brand-bg"
            />
        </svg>
    );
}
