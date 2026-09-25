# Blueprint

A starting point for new products: a Laravel 13 + Inertia 3 + React 19 + shadcn/ui monolith with
the Claude Code harness, the architecture, the tooling and the house style of a production app
already in place — and no product in it yet.

## Starting a new product

1. Copy this repository (a fresh `git clone` into a new folder, then point `origin` at the new
   remote).
2. `composer setup` — install, key, migrate + seed, build.
3. Open Claude Code in the folder and run the **`start-project`** skill. It names the product,
   drafts the requirement catalogue with you, fills the glossary and the first decisions, sets the
   locales, and leaves every gate green.
4. From then on: **`implement-requirement`** for each slice, **`create-screen`** for each new page.

## Getting started

```bash
composer setup     # install, generate key, migrate + seed, build
composer dev       # serve + queue + Vite on http://localhost:8000
```

Log in as `test@example.com` (admin) or `user@example.com`, password `password` (seeded by
`php artisan migrate:fresh --seed`). There is no self-registration: admins create accounts under
Settings → Users, and `php artisan users:create-admin` creates the first admin of an installation.
`/_primitives` (outside production) shows every core UI primitive in the real shell.

Requires PHP 8.5, Node 22, Composer 2 and `jq` (for the Claude hooks). See
[`docs/STACK.md`](docs/STACK.md) for exact versions.

## Where things are

| Path | What it is |
| --- | --- |
| [`CLAUDE.md`](CLAUDE.md) | Architecture, testing approach and conventions — start here before changing code |
| [`.claude/`](.claude/README.md) | Shared agent setup: path-scoped rules, skills and the verification hooks |
| [`docs/REQUIREMENTS.md`](docs/REQUIREMENTS.md) | The requirement catalogue — the contract; empty until a project starts |
| [`docs/DECISIONS.md`](docs/DECISIONS.md) | The decisions in force (`B*` blueprint, `D*` project) and the questions still open |
| [`docs/GLOSSARY.md`](docs/GLOSSARY.md) | Domain term → identifier, and area prefix → namespace |
| [`docs/STACK.md`](docs/STACK.md) | Exact installed versions and the conventions set up front |
| [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md) | Hosting options, cost estimate and go-live steps (proposal) |
| [`docs/spec/status.txt`](docs/spec/status.txt) | Implementation status, one line per requirement |
| [`docs/spec/COVERAGE.md`](docs/spec/COVERAGE.md) | Generated report — which tests cite which requirement |
| `app/Domain/` | The requirements' rules, framework-free, by area |
| `app/Http/{Area}/{Action}/` | Invokable controller + FormRequest + Service per action |
| `app/Spec/` | The tooling behind `spec:index` / `spec:coverage` |
| `resources/css/theme.css` | The design tokens |
| `resources/js/components/core/` | Behavioural UI primitives: dialogs, toast, panel, autosave, undo, sortable list … |
| `resources/js/layouts/shell/` | The app shell: navigation rail, header trail, tab bar, user chip |
| `lang/` | The one translation catalogue for server and client |

## Checks

```bash
composer ci:check   # everything CI runs: format, lint, types, spec gate, PHP + JS tests
composer test       # Pint, PHPStan, the spec gate, Pest
npm run check       # format + lint + type check (add --fix to apply)
npm run test        # frontend unit tests
npm run test:e2e    # Playwright
```

Requirement traceability:

```bash
php artisan spec:index      # read docs/REQUIREMENTS.md into the status ledger
php artisan spec:coverage   # per-area progress; fails if a `done` requirement has no test
```

## Working against the requirements

Every requirement has a stable id of the form `AREA-NN` (`INV-04`). Quote it in branch names
(`feat/INV-04-invoice-numbers`), commit subjects (`INV-04: gapless invoice numbers`), test
descriptions and code comments explaining why a rule looks the way it does.

Naming an id in a test is what registers it as covered — `spec:coverage` reads those citations and
fails when anything marked `done` in `docs/spec/status.txt` has no test behind it. Update the
ledger in the same change as the code.

**`docs/` is not formatted.** The catalogue is laid out by hand; the formatter leaves `docs/**`
and Markdown alone.
