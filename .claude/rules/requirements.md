---
paths:
  - 'app/**'
  - 'resources/js/**'
  - 'tests/**'
---

# The spec is the contract

Every rule in this app comes from one of two places:

1. `docs/REQUIREMENTS.md` — the numbered functional requirements, ids of the form `ABC-01`
   (a 2–4 letter area prefix and a two-digit number).
2. `docs/DECISIONS.md` — the decisions in force, cited by their id (`D1`, `B3`, …). A decision
   settles *how*; a requirement says *what*.

## Never invent behaviour

A made-up default, threshold, rate, limit, label, ordering or list of options is the most damaging
thing you can add here, because it looks right and no test will contradict it. If you cannot
point at a requirement id or a decision for a value, you do not know it — ask.

**When the requirement is silent or ambiguous, stop and ask.** Do not pick a plausible answer and
move on. The user's answer becomes either a sharper requirement (their call to write — a hook asks
before `docs/REQUIREMENTS.md` changes) or a decision in `docs/DECISIONS.md`.

Never edit `docs/REQUIREMENTS.md` to make it agree with the code. If the code and the requirement
disagree, one of them is wrong, and which one is the user's call.

## Cite the requirement id

Every test that implements a requirement names its id — in the `describe`/`it` description or a
comment above the case. That citation is what `php artisan spec:coverage` reads; there is no
annotation API and no plugin, just the id appearing in the file. It works identically for Pest,
Vitest and Playwright.

```php
describe('INV-04 · invoice numbers', function () {
    it('is gapless within a calendar year', function () { … });
});
```

Cite ids in production code too, as a comment, wherever a line exists only because a requirement
says so. A future reader needs to know that `0.005` is `INV-07` and not a guess.

## Keep the status ledger honest

`docs/spec/status.txt` carries one line per requirement. Update it in the same change as the code.
`php artisan spec:index` adds new ids from the catalogue as `planned`; statuses are edited by hand.

| Status | Means |
| --- | --- |
| `planned` | Not started. |
| `in-progress` | Part of the rule exists. A helper that is not wired into a screen yet is **in-progress**, not done. |
| `done` | The requirement is met in the app **and** a test cites it. The gate enforces the test half. |
| `changed` | Deliberately implemented differently from how it is written. **Needs a reason.** |
| `wont-do` | Deliberately not built. **Needs a reason.** |

`changed` and `wont-do` need reasons because a reviewer reading `REQUIREMENTS.md` a year from now
will otherwise read them as missing features.

Do not mark something `done` to make the report look better. The report is for us.

## UI text is part of the requirement

Where a requirement quotes a label, a message or a status name, that wording is binding — in the
requirement's language. It becomes the translation for that locale in `lang/{locale}.json`, copied
verbatim, under an English key. Identifiers stay English — see `docs/GLOSSARY.md` for the fixed
domain vocabulary, and add to it in the same change when you need a term it does not cover.
