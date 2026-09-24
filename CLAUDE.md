# CLAUDE.md

Guidance for Claude Code working in this repository.

## Read this first

Every rule in this app traces back to a numbered requirement in `docs/REQUIREMENTS.md` or a
decision in `docs/DECISIONS.md`. Inventing a plausible default, threshold, label or ordering is
the most damaging thing you can do here, because it looks correct and no test will contradict it.
When the spec is silent, ask.

- `.claude/rules/requirements.md` — how the catalogue, the decisions and the status ledger work
  together. Loaded automatically for `app/`, `resources/js/` and `tests/`. **Read it before your
  first edit.**
- `docs/GLOSSARY.md` — the fixed domain-term → identifier mapping and the area → namespace map.
  Look a term up rather than naming it yourself.
- `docs/DECISIONS.md` — the decisions in force (cited by id: `B6`, `D2`, …) and the questions still
  open.

**A fresh copy of the blueprint has an empty catalogue.** If `docs/REQUIREMENTS.md` declares no
requirements yet, run the `start-project` skill before building anything.

## What this is

<!-- start-project replaces this section with the product: its name, who uses it, what it does. -->

A blueprint for new products: a Laravel 13 + Inertia 3 + React 19 + shadcn/ui monolith with the
house style, the architecture, the tooling and the Claude harness already in place, and no product
in it yet.

## Commands

```bash
composer setup            # from a fresh clone: install, key, migrate + seed, build
composer dev              # serve + queue + Vite
composer test             # Pint, PHPStan, spec gate, Pest (unit + feature + architecture)
composer ci:check         # everything CI runs except the e2e job

npm run check             # format + lint + type check (add --fix to apply)
npm run test              # frontend unit tests (vite-plus / Vitest)
npm run test:e2e          # Playwright, boots its own server

php artisan spec:index    # read docs/REQUIREMENTS.md into the status ledger
php artisan spec:coverage # the traceability gate; rewrites docs/spec/COVERAGE.md

php artisan migrate:fresh --seed   # reset; dev login test@example.com (admin) or user@example.com, password `password`
php artisan users:create-admin     # the first admin of an installation (B14)

php artisan route:list --path=users              # a route's name, action and middleware
php artisan config:show app.locales              # a resolved config value
php artisan tinker --execute 'echo User::count();'   # single quotes outside, so $ and " survive the shell
```

Run the gates through the scripts, never `npx eslint` / `npx vitest` — this project has neither.
`vite-plus` (`vp`) provides the test runner, linter and formatter; a hook refuses the `npx` forms
because they resolve unpinned versions and drop the flags. See `docs/STACK.md`.

Single test: `./vendor/bin/pest --filter=StatusLedgerTest` · `npm run test -- dialogs`.

## Verifying changes (do before considering a task done)

```bash
composer test                      # always
npm run check && npm run test      # if you touched resources/js
php artisan spec:coverage          # if you implemented or retired a requirement
```

A `Stop` hook re-runs the static half (Pint, PHPStan, lint, `tsc`, `spec:coverage`, the
translation check) over the files you changed and **blocks** the turn on failure. It does not run
the test suites — that is on you.

Rules of thumb:

- Implemented a rule → run its unit test **and** update `docs/spec/status.txt`.
- Touched a screen → run the feature test and `npm run check`.
- Added UI text → the key is in every `lang/{locale}.json`.
- Changed a hook → `bash .claude/hooks/smoke-test.sh`.

## Architecture

Full detail in `.claude/rules/architecture.md` (backend) and `.claude/rules/react.md` (frontend).
The shape:

```text
app/
├─ Domain/{Area}/          the requirements' rules — pure PHP, no framework
│  └─ Shared/              value objects and rules two or more areas use
├─ Http/{Area}/{Action}/   invokable controller (HTTP edge) + FormRequest + Service (orchestration)
│  └─ ../Ports/           the area's only public surface — how another area calls in
├─ Models/                 Eloquent
└─ Spec/                   requirement-catalogue tooling (not product code)

resources/js/
├─ pages/{area}/           Inertia pages — thin: take props, compose
├─ features/{area}/        that area's components and hooks — the actual work
├─ components/core/        cross-area behavioural primitives (dialogs, toast, panel, autosave …)
├─ components/ui/          shadcn — generated, do not hand-edit
├─ layouts/shell/          the app shell: navigation rail, header trail, tab bar, user chip
└─ lib/                    pure functions: i18n, number formatting, pinned mirrors of domain rules

lang/{locale}.json         the one translation catalogue, server and client
resources/css/theme.css    the design tokens
```

`{Area}` is one of the catalogue's own areas; `docs/GLOSSARY.md` maps prefix → namespace. Do not
invent an area name.

A service depends on services **in its own area only**. What one area lets another do, it publishes
as a `Port` in its own `Ports/` folder; everything behind that is private, and
`tests/Architecture/AreaBoundariesTest.php` enforces it. Most cross-area needs are rules, not Ports
— those belong in `app/Domain/`, which anything may import.

**The load-bearing rule: `app/Domain` must run without booting Laravel.** No Eloquent, no
`Request`, no facades, no Inertia — `tests/Architecture/BoundariesTest.php` enforces it. That is
what lets the rules run as fast unit tests over large datasets, and what stops a calculation rule
from quietly acquiring a database round-trip.

Laravel 13 slim skeleton: middleware and exceptions are configured in `bootstrap/app.php`, **not**
`app/Http/Kernel.php`.

## Testing approach

Four layers, chosen by where the rule lives — see `.claude/rules/testing.md`.

| Layer | Where | Runner |
| --- | --- | --- |
| Domain | `tests/Unit/Domain/{Area}/` | Pest — no DB, no HTTP. Most requirements land here. |
| Feature | `tests/Feature/{Area}/` | Pest — routes, validation, Inertia props |
| Frontend | `resources/js/**/*.test.tsx` | `vp test` — interactive primitives, hooks, TS mirrors |
| End-to-end | `tests/e2e/*.spec.ts` | Playwright — whole workflows |

PHP tests **mirror** the app structure, they are not colocated:
`app/Domain/Billing/InvoiceNumber.php` → `tests/Unit/Domain/Billing/InvoiceNumberTest.php`.

- **Cite the requirement id** in the `describe`/`it` description or a comment. `spec:coverage`
  reads those citations textually — no annotation API, works the same for Pest, Vitest and
  Playwright — and fails when anything marked `done` has no test.
- **Prefer a golden vector to a hand-written case.** Where a reference answer set exists, freeze it
  in `tests/Fixtures/` and assert against it with a dataset.

## The status ledger

`docs/spec/status.txt`, one line per requirement, updated in the same change as the code.

`planned` · `in-progress` · `done` · `changed` · `wont-do`

`done` means the requirement is met **in the app** and a test cites it — a helper no screen uses
yet is `in-progress`. `changed` and `wont-do` need a reason on the line, because a reviewer reading
`REQUIREMENTS.md` a year from now would otherwise read them as missing features.

Do not mark something `done` to make the report look better. The report is for us.

## Translations

Every user-facing string goes through `t('…')` in React and `__('…')` in PHP; keys are the English
text; every other locale has an entry in `lang/{locale}.json`. There is one catalogue for both
sides and no i18n library — see `.claude/rules/i18n.md`. Where a requirement quotes UI text, that
wording is the translation for its language, verbatim. Identifiers are English per
`docs/GLOSSARY.md`.

## Do NOT

- **Invent a business rule, default, threshold, limit, label or ordering.** If you cannot cite a
  requirement id or a decision id for a value, ask.
- **Change `docs/REQUIREMENTS.md` to fit the code.** It is the contract; a hook asks the user
  before every write to it. Record a deliberate difference as `changed` in the ledger instead.
- Put an editable default in `config/`. A value the team can change from a screen is data.
- Let `app/Domain` touch Eloquent, `Request`, facades or Inertia.
- Let a service import another area's service. Cross an area through that area's `Port`, or
  move the rule to `app/Domain` where it probably belongs.
- Put business logic in a controller — it belongs in `app/Domain/{Area}/`.
- Return a raw Eloquent model as an Inertia prop.
- Generate a PHPStan baseline, or add `mixed`/a cast/a widened type to silence a finding. There is
  no baseline file and there must not be one; a hook refuses the flag.
- Write a bare user-facing string in a component, or a computed key in `t()`.
- Hardcode a colour, radius or `820px`/`480px` breakpoint — they are tokens in
  `resources/css/theme.css`.
- Add a dependency without approval. Check `docs/STACK.md` first — `sonner`, `@dnd-kit/*`,
  `date-fns` and the shadcn components are already installed.
- Parameterise a constant the spec states as fixed.
- Write a throwaway verification script when a test proves the same thing.
- Add comments that explain what the code does. Reserve them for the non-obvious *why* — which is
  usually "this requirement says so", with the id.

## Working agreements

- Follow the conventions of the files around you: read a sibling before creating anything.
- Look for an existing value object, primitive or component to reuse before writing one.
- Do not add base directories or change dependencies without approval.
- Create documentation files only when asked.
- Be concise; skip the obvious.
- Questions listed under „Still open“ in `docs/DECISIONS.md` are not yours to settle. Stop at that
  boundary and ask.

## Path-scoped rules

These load automatically when a matching file is touched, so they are not repeated here. See
`.claude/README.md` for the full map, the `jq` requirement and how to debug a hook.

| Rule | Applies to |
| --- | --- |
| `.claude/rules/requirements.md` | `app/`, `resources/js/`, `tests/` — the spec as contract |
| `.claude/rules/architecture.md` | `app/`, `routes/`, `database/`, `config/` — backend |
| `.claude/rules/react.md` | `resources/js/`, `resources/css/` — frontend and the house style |
| `.claude/rules/i18n.md` | `resources/js/`, `resources/views/`, `app/`, `lang/` — translations |
| `.claude/rules/testing.md` | `tests/`, `*.test.ts(x)` |
| `.claude/rules/php.md` | `**/*.php` |
| `.claude/rules/minimalism.md` | `app/`, `resources/js/` |

## Skills

| Skill | For |
| --- | --- |
| `start-project` | Once, on a fresh copy: name the product, draft the catalogue with the user, glossary, decisions, locales |
| `implement-requirement` | The repeated task: take requirement ids to done, ledger included |
| `create-screen` | Scaffold an Inertia screen — controller, route, page, props, translations, tests |

Reference and workflow skills load on demand from their descriptions — the full list is in
`.claude/README.md`. The ones worth reaching for by name: `grilling` (a question round before
building), `domain-modeling` (glossary and decisions), `diagnosing-bugs`, `tdd`,
`receiving-code-review`, `finishing-a-development-branch`, and the stack references
`laravel-best-practices`, `testing-best-practices`, `inertia-react-development`,
`fortify-development`, `laravel-permission-development`.

## Agents

Subagents in `.claude/agents/` do the work in their own context and hand back only a report.

| Agent | For | Model |
| --- | --- | --- |
| `explorer` | Locating code, mapping an area — read-only | sonnet |
| `reviewer` | Diff review for what no gate sees: requirement fidelity, invented values, authorization, props exposure, test honesty — read-only | opus |
| `gate-fixer` | Taking a failing gate back to green without gaming it | sonnet |
| `test-author` | Writing tests from the requirement, not the implementation | sonnet |

## Automated gates

`.claude/settings.json` enforces what this file would otherwise only request:

- Every `.php` / `.ts(x)` / `.css` edit is formatted automatically (Pint or `vp fmt`). `docs/` is
  never formatted.
- Every edit under `app/` or `resources/js/` gets a prompt review for **invented behaviour** and
  **untranslated UI text**.
- At the end of a turn, Pint, PHPStan, `vp lint`, `tsc`, `spec:coverage` and the translation check
  run over the files you changed and **block** on failure.
- Writes to `docs/REQUIREMENTS.md` ask the user first. Reads of `.env*`, edits of the generated
  `docs/spec/COVERAGE.md`, a PHPStan baseline and `npx`-invoked gates are denied outright.
