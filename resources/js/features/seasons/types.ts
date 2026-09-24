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
    /** STAT-11, STAT-13 — null until the matchday is complete. */
    highlights: {
        winners: { playerId: number; name: string }[];
        lanterns: { playerId: number; name: string }[];
        average: number;
        /** STAT-13 — the money that went into the box on this matchday. */
        penaltyCents: number;
    } | null;
};

/** STAT-14 — the money of one complete matchday and the box's balance after it. */
export type PenaltyBoxMatchday = {
    matchday: number;
    cents: number;
    cumulativeCents: number;
};

/** Mirrors App\Domain\Statistics\PenaltyBox (STAT-12, STAT-14). */
export type PenaltyBox = {
    totalCents: number;
    firstHalfCents: number | null;
    secondHalfCents: number | null;
    payers: { playerId: number; name: string; penaltyCents: number }[];
    matchdays: PenaltyBoxMatchday[];
};

/** A player to pick for a season (SEA-02). */
export type PlayerOption = { id: number; name: string; alias: string };
