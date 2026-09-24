---
name: start-project
description: Turn a fresh copy of this blueprint into a new product — name it, draft the requirement catalogue with the user, fix the glossary and the area namespaces, record the first decisions, set the locales and the brand palette, and leave every gate green. Use when the user has just copied the blueprint for a new project, says "start a new project/product from this", or the catalogue in docs/REQUIREMENTS.md is still the empty template.
---

# Start a project from the blueprint

The blueprint ships the harness, the stack and the house style — and no product. This skill puts a
product into it without inventing one: every rule the app will get comes out of a conversation
with the user, written down before any code is.

Work through the steps in order. Each ends in a file the user can read and correct.

## 0. Check the starting point

```bash
git log --oneline | head -5
grep -c '^- \*\*[A-Z]\{2,4\}-[0-9]\{2\}\*\*' docs/REQUIREMENTS.md   # 0 in a fresh blueprint
composer test && npm run check && npm run test                       # green before you start
```

If the catalogue already has requirements, this project has started — use `implement-requirement`
instead.

## 1. Name it

Ask for: the product name, a one-sentence description, the organisation, and the Composer vendor
name. Then replace the blueprint's placeholders:

| Where | What |
| --- | --- |
| `composer.json` | `name`, `description`, `keywords`, `license` |
| `package.json` | `name` |
| `.env.example`, `.env` | `APP_NAME` |
| `config/app.php` | the `name` fallback |
| `README.md` | title and the first paragraph |
| `CLAUDE.md` | „What this is“ |
| `database/seeders/DatabaseSeeder.php`, `tests/e2e/support/auth.ts` | the dev login, if they want a different one |

The mark in the navigation rail and on the auth screens is the app name's initials on the brand
tile (`components/app-logo-icon.tsx`, `layouts/shell/sidebar.tsx`). A real logo replaces those
two and `public/favicon.svg`.

## 2. Draft the requirement catalogue — with the user

This is the step that matters. Interview the user area by area; do not draft rules they did not
state.

1. **Areas first.** Ask what the product does and cut it into 5–15 functional areas. Give each a
   2–4 letter prefix (`ACC` access, `INV` invoices …) and an English namespace.
2. **Per area, the rules.** For each area ask for the screens, the data, the rules and limits, the
   statuses and their transitions, who may do what, and the exact wording of anything a user or a
   document reads. Push for numbers: "a few" and "usually" are not requirements.
3. **Write it** into `docs/REQUIREMENTS.md`, keeping the template's format exactly — the parser
   reads it:
   - an area heading: `## 3. Invoices (`INV`)`
   - one bullet per requirement: `- **INV-01** — …`, continuation lines indented two spaces
   - constant tables at the top of the area they belong to
   - quoted UI text verbatim, in the language the user gave it
4. **Read it back.** Show the user the catalogue and let them correct it before anything is built
   on it. A hook asks for their yes on every write to this file; that is intended.

What the user leaves undecided goes into `docs/DECISIONS.md` → „Still open“, not into the
catalogue as a guess.

## 3. Index it

```bash
php artisan spec:index       # every id into docs/spec/status.txt as `planned`
php artisan spec:coverage    # the progress table, and docs/spec/COVERAGE.md
```

## 4. Glossary and namespaces

Fill `docs/GLOSSARY.md`:

- **Area → namespace**: every prefix from step 2 with its `App\Domain\{Area}` / `App\Http\{Area}`
  / `resources/js/features/{area}` names.
- **Core entities**: the domain terms in the user's language → the English identifier used in
  code, with one line on what it is and what it is *not* (the confusable neighbour).
- **Terms of art that stay untranslated**, if any, with why.

## 5. Decisions

In `docs/DECISIONS.md`, keep the blueprint's decisions (`B*`) that still hold and record the
project's first ones (`D1`, `D2`, …): production database, hosting, auth changes (the blueprint
has no self-registration and ships the `admin`/`user` roles — B13, B14; say if the product differs),
external services. Every open question from step 2 goes under „Still open“.

## 6. Locales

Ask which languages the UI ships in and which is the default.

- `config/app.php` → `locales` and the `APP_LOCALE` / `APP_FALLBACK_LOCALE` defaults.
- One `lang/{locale}.json` per non-English locale, and `lang/{locale}/*.php` for Laravel's own
  messages (copy them from a maintained source; do not machine-translate validation messages).
- Drop a locale the product does not ship: its JSON, its folder, its entry in `locales`.
- `playwright.config.ts` → `locale` / `timezoneId`, and `APP_TIMEZONE`.

## 7. Brand palette (only if the user asks)

The blueprint's palette is the house style and the default. To change it, edit only
`resources/css/theme.css` (the `--color-brand-*` tokens) and the matching shadcn variables in
`resources/css/app.css` (`:root` and `.dark`), plus the two background colours in
`resources/views/app.blade.php`. Keep the token *names*; components read them.

## 8. Verify and hand over

```bash
composer test && npm run check && npm run test && npm run test:e2e
php artisan spec:coverage
```

Commit as one change („Start <product> from the blueprint“). Then report: the areas and the number
of requirements per area, the decisions recorded, the questions still open, and a proposed first
slice for `implement-requirement` — usually access and the one entity everything else hangs off.
