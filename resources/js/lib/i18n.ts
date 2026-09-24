/**
 * B6 — the client half of Laravel's JSON translations. The server shares the
 * active locale's `lang/{locale}.json` as the `i18n` prop; this looks a key up
 * in it and fills the placeholders the way `__()` does, so a key reads the
 * same on both sides.
 */
export type Catalogue = Readonly<Record<string, string>>;

export type Replacements = Readonly<Record<string, string | number>>;

const upperFirst = (value: string): string =>
    value.charAt(0).toUpperCase() + value.slice(1);

const escape = (value: string): string =>
    value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

/**
 * A key with no entry falls back to itself: keys are the English source text,
 * so English needs no catalogue of its own.
 *
 * `:name` is replaced as given, `:Name` capitalised and `:NAME` upper-cased —
 * Laravel's `makeReplacements()`. Like its `strtr()`, the longest placeholder
 * wins, so `:names` is never read as `:name` followed by an „s“.
 */
export function translate(
    catalogue: Catalogue,
    key: string,
    replacements: Replacements = {},
): string {
    const line = catalogue[key] ?? key;
    const map = new Map<string, string>();

    for (const [name, raw] of Object.entries(replacements)) {
        const value = String(raw);
        map.set(`:${upperFirst(name)}`, upperFirst(value));
        map.set(`:${name.toUpperCase()}`, value.toUpperCase());
        map.set(`:${name}`, value);
    }

    if (map.size === 0) {
        return line;
    }

    const pattern = new RegExp(
        [...map.keys()]
            .sort((a, b) => b.length - a.length)
            .map(escape)
            .join('|'),
        'g',
    );

    return line.replace(pattern, (placeholder) => map.get(placeholder) ?? '');
}

/**
 * Marks a key that is written in one place and translated in another — a
 * page's static `layout` props, a nav item's title — so that
 * tests/Architecture/TranslationsTest.php still sees it as a literal. The
 * value is the key itself; whoever renders it calls `t()` on it.
 */
export const i18nKey = (key: string): string => key;
