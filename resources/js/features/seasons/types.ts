/** Mirrors App\Http\Seasons\ShowSeason\ShowSeasonService. */
export type SeasonOption = { id: number; name: string };

export type SeasonSummary = SeasonOption & {
    penaltyStartCents: number;
    penaltyStepCents: number;
};

export type StandingLine = {
    playerId: number;
    name: string;
    alias: string;
    place: number;
    points: number;
    penaltyCents: number;
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
};

/** A player to pick for a season (SEA-02). */
export type PlayerOption = { id: number; name: string; alias: string };
