---
name: implement-requirement
description: Implement one or more numbered requirements from docs/REQUIREMENTS.md — read the whole area, resolve every open question with the user, implement in the right layer, write a test citing the id, add the translation keys, and update the status ledger. Use when the user names a requirement id (INV-04, "do the ACC area"), asks to implement a spec area, or asks what to work on next.
---

# Implement a requirement

The repeated task in this repo. The argument is one or more requirement ids, an area prefix, or a
description of the behaviour. If none was given, run `php artisan spec:coverage` and propose the
next slice from what is still `planned` — smallest dependency first (a rule other areas use before
the screens that use it).

## 1. Read the requirement — and its whole area

```bash
grep -n 'INV-04' docs/REQUIREMENTS.md       # the requirement and its neighbours
grep -n 'INV-04' -r app resources/js tests  # anything already citing it
```

Read the **whole area section**, not just the one bullet — requirements in an area constrain each
other, and the constant tables at the top of a section carry the numbers. Read the decisions it
touches in `docs/DECISIONS.md`.

## 2. Find the gaps before writing code

List every value, label, ordering, limit and edge case the implementation needs, and mark where
each one comes from. Anything that is not stated in a requirement or a decision is a **question
for the user**, not a gap to fill with something plausible. Ask them together, in one round —
the `grilling` skill runs that round (each question with your recommended answer; facts you look
up yourself, decisions go to the user). For a term the glossary lacks, `domain-modeling`.

The user's answers go on record before the code is written:

- A sharper statement of *what* → the user updates `docs/REQUIREMENTS.md` (a hook asks for their
  explicit yes). Offer the wording; do not slip it in.
- A choice of *how* → a new entry in `docs/DECISIONS.md` „Decisions in force“, with an id.
- Still undecided → an entry under „Still open“, and stop at that boundary.

## 3. Decide the layer

| The rule is | It goes in |
| --- | --- |
| A calculation, a derivation, a format, a validation rule over values | `app/Domain/{Area}/` |
| Used by two or more areas | `app/Domain/Shared/` |
| A route, validation, or assembling props | `app/Http/{Area}/{Action}/` — the controller and Request |
| Persisting it: a transaction, model writes, an audit entry | `app/Http/{Area}/{Action}/{Action}Service.php` |
| Something another area must be able to trigger | a `Port` in the owning `app/Http/{Area}/Ports/` |
| Interaction behaviour (drag, undo, autosave, confirm) | `resources/js/components/core/` |
| Rendering | `resources/js/features/{area}/` |

`docs/GLOSSARY.md` maps the area prefix to the namespace and fixes the domain vocabulary. Do not
invent a name for a domain term — add it to the glossary in this change if it is missing.

The domain layer must not touch Eloquent, `Request`, facades or Inertia; `tests/Architecture/`
enforces it. If a rule seems to need the database, it usually needs its inputs passed in instead.

Services stay inside their area — one area reaches another only through a `Port`, also enforced
there. A requirement that spans areas is almost always a **domain** rule, not a Port: put it in
`app/Domain/{Area}/` and let both areas call it.

A new screen → follow the `create-screen` skill for its shape.

## 4. Implement, small

Follow the minimalism ladder (`.claude/rules/minimalism.md`). A value the requirement states as
fixed stays a constant; an editable default is shared-settings data, never `config/`.

Cite the source in a comment wherever a line exists only because a requirement or decision says so:

```php
// INV-07 — amounts round half-up to the cent *per line*, not on the total (D4).
```

Every user-facing string through `t()` / `__()`, with the key in every `lang/{locale}.json`.
Where the requirement quotes the wording, that wording is the translation for its language.

## 5. Test, citing the id

Layer and placement per `.claude/rules/testing.md` — mirror the app structure. The id goes in the
`describe`/`it` description or a comment above the case; `spec:coverage` reads it textually.

Where a reference answer set exists (a spreadsheet the business trusts, an existing system's
output), freeze it in `tests/Fixtures/` and run the rule against it as a dataset.

Assert UI text **only** against wording you have read in the requirement or the translation file.
Copying an expectation out of your own implementation proves nothing.

For a larger slice, delegate the write-run-iterate loop to the `test-author` agent with the ids —
it derives expectations from the requirement, not from your code. `tdd` is the skill for working
test-first.

## 6. Update the status ledger

Edit `docs/spec/status.txt` in the same change:

- `done` — the requirement is met **in the app** and a test cites it. A helper that no screen uses
  yet is `in-progress`, not done. The gate checks the test half; only you can check the other half.
- `changed` / `wont-do` — needs a reason on the same line.

Then:

```bash
php artisan spec:coverage        # rewrites docs/spec/COVERAGE.md, fails on an inconsistency
```

## 7. Verify

```bash
composer test        # Pint, PHPStan, spec gate, Pest (unit + feature + architecture)
npm run check        # format + lint + types, if you touched resources/js
npm run test         # frontend, if you touched resources/js
npm run test:e2e     # if the requirement is a user workflow
```

The Stop gate re-runs the static half over your changed files and blocks on failure. Tests it does
not run — run them yourself. A failing gate whose dump would swamp this session → the `gate-fixer`
agent.

## 8. Review

For anything beyond a one-line rule, hand the diff and the ids to the `reviewer` agent — it checks
what no gate can: fidelity to the requirement, invented values across the whole change,
authorization, props exposure, test honesty. Treat its findings per `receiving-code-review`: verify
each against the code and the spec, fix the real ones, and bring anything the spec does not settle
to the user as a question.

## Report

Say which ids you moved and to what status, name the decisions you recorded, and list anything
still open. If a requirement turned out to depend on an open decision, stop at that boundary and
say so rather than picking for the user.
