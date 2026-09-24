/** Mirrors App\Domain\Statistics\MatchdayLine (STAT-04–06). */
export type MatchdayLine = {
    matchday: number;
    points: number;
    leagueAverage: number;
    dayPlace: number;
    overallPlace: number;
    penaltyCents: number;
    cumulativePenaltyCents: number;
};

/** Mirrors App\Domain\Statistics\FormGrade (STAT-07). */
export type FormGrade = 'good' | 'middle' | 'bad';

/** Mirrors App\Domain\Statistics\PlayerSeasonStats (STAT-03–07). */
export type PlayerSeasonStats = {
    matchdaysPlayed: number;
    totalPoints: number;
    averagePoints: number | null;
    bestPoints: number | null;
    bestMatchdays: number[];
    worstPoints: number | null;
    worstMatchdays: number[];
    place: number;
    penaltyCents: number;
    firstHalfPenaltyCents: number | null;
    secondHalfPenaltyCents: number | null;
    lines: MatchdayLine[];
    wins: number;
    lanterns: number;
    penaltyFreeMatchdays: number;
    longestPenaltyFreeStreak: number;
    form: { matchday: number; place: number; grade: FormGrade }[];
};

/** Mirrors App\Domain\Statistics\CareerStats (STAT-08). */
export type CareerStats = {
    seasonsPlayed: number;
    averagePlace: number | null;
    totalPoints: number;
    totalPenaltyCents: number;
    totalWins: number;
};

/** Mirrors App\Domain\Statistics\HeadToHead (STAT-09). */
export type HeadToHead = {
    lines: { matchday: number; a: number; b: number }[];
    aAhead: number;
    level: number;
    bAhead: number;
};

/** Mirrors App\Domain\Statistics\RecordHolder and LeagueRecord (STAT-10, STAT-15). */
export type RecordHolder = {
    seasonId: number;
    seasonName: string;
    playerId: number | null;
    playerName: string | null;
    matchday: number | null;
};

export type LeagueRecord = { value: number; holders: RecordHolder[] };

export type LeagueRecords = {
    highestScore: LeagueRecord | null;
    lowestScore: LeagueRecord | null;
    mostWins: LeagueRecord | null;
    highestPenalty: LeagueRecord | null;
    closestMatchday: LeagueRecord | null;
    /** STAT-15 */
    mostExpensiveMatchday: LeagueRecord | null;
};

export type Option = { id: number; name: string };
