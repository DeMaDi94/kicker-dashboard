/** Mirrors App\Domain\Visits\PublicPage (VIS-01). */
export type PublicPage = 'season' | 'player' | 'compare' | 'records';

/** One day of the period (VIS-05). */
export type VisitDay = {
    date: string;
    visits: number;
    visitors: number;
};

/** Mirrors App\Domain\Visits\VisitStatistics (VIS-05, VIS-06). */
export type VisitStatistics = {
    visits: number;
    visitors: number;
    days: VisitDay[];
    /** Monday to Sunday, each hour 0 to 23. */
    heatmap: number[][];
    /** Hour 0 to 23. */
    hours: number[];
    /** The most visited first. */
    pages: { page: PublicPage; visits: number }[];
};
