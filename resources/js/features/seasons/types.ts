/** Mirrors App\Http\Seasons\ShowSeason\ShowSeasonService. */
export type SeasonOption = { id: number; name: string };

export type SeasonSummary = SeasonOption & {
    penaltyStartCents: number;
    penaltyStepCents: number;
    /** PEN-04 — the last matchday of the „Hinrunde“, or none set. */
    settlementMatchday: number | null;
};

export type StandingLine = {
    playerId: number;
    name: string;
    alias: string;
    place: number;
    points: number;
    penaltyCents: number;
    firstHalfPenaltyCents: number | null;
    secondHalfPenaltyCents: number | null;
};

export type MatchdayLine = {
    playerId: number;
    name: string;
    alias: string;
    points: number | null;
    place: number | null;
    penaltyCents: number | null;
};

export type MatchdayBlock = {
    number: number;
    complete: boolean;
    hasPoints: boolean;
    rows: MatchdayLine[];
    /** STAT-11 — null until the matchday is complete. */
    highlights: {
        winners: { playerId: number; name: string }[];
        lanterns: { playerId: number; name: string }[];
        average: number;
    } | null;
};

/** Mirrors App\Domain\Statistics\PenaltyBox (STAT-12). */
export type PenaltyBox = {
    totalCents: number;
    firstHalfCents: number | null;
    secondHalfCents: number | null;
    payers: { playerId: number; name: string; penaltyCents: number }[];
};

/** A player to pick for a season (SEA-02). */
export type PlayerOption = { id: number; name: string; alias: string };
