export type KickerRow = { name: string; points: number };

export type KickerMatch = {
    points: Map<number, number>;
    missing: { id: number; name: string }[];
    unknown: string[];
};

const PLACE = /^(\d+|[–-])$/;
const POINTS = /^[-−]?\d+$/;

/*
 * MD-05, D12 — the „Spieltagswertung“ block of the league page as kicker
 * copies it: a header „Platz Name Punkte“, then place, name and points per
 * row; a tied place reads „–“. The block ends where a row stops looking like
 * one — on the page, at „Saisonwertung“. Null when the text has no such block.
 */
export function readMatchdayRanking(text: string): KickerRow[] | null {
    const tokens = text
        .split(/\t|\r?\n/)
        .map((token) => token.trim())
        .filter((token) => token !== '');
    const start = tokens.indexOf('Spieltagswertung');

    if (start === -1) {
        return null;
    }

    let at = start + 1;

    if (
        tokens[at] === 'Platz' &&
        tokens[at + 1] === 'Name' &&
        tokens[at + 2] === 'Punkte'
    ) {
        at += 3;
    }

    const rows: KickerRow[] = [];

    for (;;) {
        const place = tokens[at];
        const name = tokens[at + 1];
        const points = tokens[at + 2];

        if (
            place === undefined ||
            name === undefined ||
            points === undefined ||
            !PLACE.test(place) ||
            !POINTS.test(points)
        ) {
            break;
        }

        rows.push({ name, points: Number(points.replace('−', '-')) });
        at += 3;
    }

    return rows.length > 0 ? rows : null;
}

/*
 * D12 — kicker marks the league's admin with „(Admin)“ behind the name; the
 * alias is compared without it, without surrounding blanks and in any case.
 */
function aliasKey(name: string): string {
    return name
        .replace(/\(Admin\)\s*$/, '')
        .trim()
        .toLocaleLowerCase('de');
}

/*
 * MD-05 — the points of each player whose alias (PLY-01) appears in the
 * ranking, the players it lacks, and the names that belong to no player.
 */
export function matchKickerRows(
    rows: KickerRow[],
    players: { id: number; name: string; alias: string }[],
): KickerMatch {
    const byAlias = new Map<string, number>();

    for (const row of rows) {
        const key = aliasKey(row.name);

        if (!byAlias.has(key)) {
            byAlias.set(key, row.points);
        }
    }

    const points = new Map<number, number>();
    const missing: KickerMatch['missing'] = [];
    const matched = new Set<string>();

    for (const player of players) {
        const key = aliasKey(player.alias);
        const value = byAlias.get(key);

        if (value === undefined) {
            missing.push({ id: player.id, name: player.name });
        } else {
            points.set(player.id, value);
            matched.add(key);
        }
    }

    const unknown = rows
        .filter((row) => !matched.has(aliasKey(row.name)))
        .map((row) => row.name);

    return { points, missing, unknown };
}
