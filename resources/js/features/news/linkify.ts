/*
 * NEWS-01 — a news post is plain text, and the links in it are clickable.
 * The text is cut into plain runs and web addresses; a sentence's closing
 * punctuation right after an address is left out of the link.
 */

export type TextPart =
    | { kind: 'text'; value: string }
    | { kind: 'link'; value: string };

const ADDRESS = /https?:\/\/[^\s<>"]+/g;
const TRAILING = /[.,;:!?)\]'"]+$/;

export function linkify(text: string): TextPart[] {
    const parts: TextPart[] = [];
    let rest = 0;

    for (const match of text.matchAll(ADDRESS)) {
        const address = match[0].replace(TRAILING, '');
        const start = match.index;

        if (start > rest) {
            parts.push({ kind: 'text', value: text.slice(rest, start) });
        }

        parts.push({ kind: 'link', value: address });
        rest = start + address.length;
    }

    if (rest < text.length) {
        parts.push({ kind: 'text', value: text.slice(rest) });
    }

    return parts;
}
