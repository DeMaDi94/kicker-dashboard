# Decisions

How the requirements are met, where more than one way was possible, and what is still undecided.
A requirement (`docs/REQUIREMENTS.md`) says *what*; a decision here says *how*, and is cited by its
id from code comments and ledger reasons.

- `B*` — decisions the blueprint made before there was a product. They hold until a project
  decision replaces one (say which, and move the old one to „Superseded“).
- `D*` — this project's own decisions, numbered in the order they were taken.

## Decisions in force

### Blueprint

| Id | Decision | Why |
| --- | --- | --- |
| B1 | **Laravel 13 slim skeleton + Inertia 3 + React 19**, one monolith. No separate SPA, no client router, no data-fetching library. | One deployable, server-owned routing and authorisation, typed routes via Wayfinder. |
| B3 | **SQLite in development and tests**; production database is a project decision. | Zero-setup local runs; tests use `:memory:`. |
| B4 | **Domain rules in `app/Domain`, framework-free**; HTTP actions in `app/Http/{Area}/{Action}` with a controller, a Request and a Service; areas talk through Ports. | See `.claude/rules/architecture.md`. Enforced by `tests/Architecture/`. |
| B5 | **Requirement traceability**: `docs/REQUIREMENTS.md` → `docs/spec/status.txt` → tests citing ids, checked by `php artisan spec:coverage` on every Stop and in `composer test`. | A rule nobody can trace back is indistinguishable from an invented one. |
| B6 | **i18n through Laravel's JSON translations**, shared with React as the `i18n` prop and read by `t()`. English keys; `de` is the default locale, `en` the fallback. The locale is chosen per browser (`locale` cookie) from `config('app.locales')`. | One catalogue for server and client, no i18n dependency. See `.claude/rules/i18n.md`. |
| B7 | **German copy uses „Du“**, including Laravel's own messages in `lang/de/*.php`. | The house tone. A project that addresses users formally changes it as a `D*` decision. |
| B8 | **The house style**: tokens in `resources/css/theme.css` — a navy primary, one saturated accent, a neutral grey ramp, one 2 px radius, one focus ring, no decorative shadows, breakpoints 820 px (`compact`) and 480 px (`phone`); faces Space Grotesk / IBM Plex Mono / Silkscreen, bundled by the Vite font plugin. The palette is drawn light-first; its dark variant inverts surfaces and ink and keeps the accent. | See `.claude/rules/react.md`. The palette is swappable; the token names are not. |
| B9 | **Interaction defaults** of the core primitives: toasts 4.5 s, a failed view's toast 9 s; autosave 300 ms debounce; undo depth 80 with 450 ms debounce; list search 300 ms debounce; live poll 6 s while visible. | Proven defaults from the product the blueprint was extracted from. Change them in the primitive with a `D*` id. |
| B10 | **Vite+ (`vp`) is the frontend toolchain** — dev server, build, test runner, linter and formatter. No separate Vitest, ESLint or Prettier. | See `docs/STACK.md`. |
| B11 | **PHPStan level 7, no baseline.** | See `.claude/rules/php.md`. |
| B13 | **Roles and permissions through `spatie/laravel-permission`.** A user holds exactly one role — `admin` or `user` (`App\Domain\Users\Role`). Code checks permissions (`users.view`, `users.create`, `users.update`, `users.delete`; restoring needs `users.delete`), never a role name; the admin role holds all four, the user role none. Roles and permissions are created by a migration, so every environment has them. The signed-in user's permissions are shared as `auth.permissions`. | A project adds a role or a permission without touching the checks. |
| B14 | **Auth is Fortify's without self-registration** (login, reset, e-mail verification, two-factor, passkeys). Accounts are created by an admin (name, address, role); the new user receives an invitation mail with a password-reset token and sets their password on Fortify's reset page — the link expires as reset links do (`config/auth.php`). Setting a password through an emailed link marks the address verified. The first admin of an installation comes from `php artisan users:create-admin`. Addresses are stored lowercased, as Fortify signs in with them. | Replaces B2. Internal products know their users; a product with public sign-up re-enables registration as a `D*` decision. |
| B15 | **Users are soft-deleted**, by an admin from the user list or by a user from their profile. A deleted user is signed out on their next request, cannot sign in (password or passkey) and can be viewed and restored by an admin — there is no permanent delete in the UI. A deleted user's address stays taken; a new account cannot reuse it. Guard rails (`App\Domain\Users\AccountGuard`): an admin cannot delete their own account from the list, cannot remove the admin role from themselves, and the last active admin can neither be deleted (from the list or the profile) nor lose the admin role. | Deleting is reversible, and an installation cannot lock itself out. |
| B16 | **The user list** is a settings section („Benutzer“), visible only with `users.view`. Columns name, email, role and email verified (the date, or „Nicht bestätigt“), every one sortable; it opens sorted by name A–Z. Search over name and email, a role filter, and a filter showing the deleted users instead of the active ones; 20 users per page. An admin edits a user's name and role (not the address) and can send the standard password-reset mail. | |

### Project

| Id | Decision | Why |
| --- | --- | --- |
| D1 | **`/` is the public season view**, reachable without signing in (ACC-01); the product is named **Vivalaraza**. | Product owner, 2026-09-24. Replaces B12. |
| D2 | **German only.** `config('app.locales')` holds `de` alone and the interface offers no language choice; `en.json` is not shipped. | Product owner, 2026-09-24 („die Oberfläche ist nur Deutsch“). Narrows B6: keys stay English, the catalogue is `lang/de.json`. |
| D3 | **Last tie-break: the name A–Z**, compared the German way (`App\Domain\Shared\NameOrder`). In the overall table it follows the penalty sum (STD-01); on a matchday it follows the place; lists of players are ordered by it alone. | Product owner, 2026-09-24. |
| D4 | **Players appear by name, with the kicker Manager alias small beside it.** The names are the short names of the league sheet (BK, FK, JLS, …). | Product owner, 2026-09-24. |
| D5 | **The overall table counts places densely** (1, 2, 2, 3), as a matchday does (MD-03). | Product owner, 2026-09-24. |
| D6 | **Amounts are stored in cents**; the season form takes euros to the cent, none below 0 € (PEN-01). The season view opens on the matchday just saved, else the last one with points, else the first; the season choice lists the newest season first. | How, not what: exact sums, and the view opens where the league currently is. Chosen while implementing; confirmed by the product owner, 2026-09-24. |
| D7 | **Every screen works on a phone**, down to a 320 px viewport: nothing scrolls sideways (wide tables scroll inside their panel), tap targets are 44 px where a list is ticked or points are entered, a player's alias sits under the name, and the points field keeps a minus key (MD-01). `tests/e2e/mobile.spec.ts` holds the first part. | Product owner, 2026-09-24 („die Ansichten sollen alle für mobil optimiert sein“). |
| D8 | **No duplicate names.** A season's name is unique among the seasons not deleted — deleting a season frees its name (SEA-06), and restoring it is refused while another season carries that name; a player is unique by name and alias together — two players may share a name if their aliases differ. Seasons: validation and the restore guard; players: validation and a unique index. | Product owner, 2026-09-24; the freed name of a deleted season the same day. |
| D9 | **Red accents, as at kicker; an anthracite ground.** The accent is a matte `#CC423C` (white text on it stays above 4.5:1), the primary ground `#1C1D21` (dark mode: the red becomes the ground); destructive moves to a darker wine red so it is not mistaken for the accent. The mark is an own drawing — a V for Vivalaraza in two round white strokes with the ball in its fork, on a red tile with softer corners (`--radius-mark`, the mark only) — in `AppLogoIcon` and `public/favicon.svg`; no kicker logo or wordmark is used. | Product owner, 2026-09-24; the red made matte the same day („moderner“). Replaces B8's navy and blue; B8's token names, radius, faces and breakpoints stay. |
| D10 | **Graphs with `recharts`** (STAT-04–06, STAT-09). Two series hues — red `--chart-1` and a muted blue `--chart-2` — validated for colour-vision deficiency against the light and the dark page; reference lines (league average, the interim settlement) are the neutral `--chart-4`, dashed. Every graph has a legend or a single named series and a tooltip; the player page also offers all matchdays as a table. | Product owner chose the library, 2026-09-24. |
| D11 | **How the statistics count** where STAT-* leaves a detail open: the form's thirds round up over the day's places (of 13 places, 1–5 good, 9–13 bad; with 4 places there is no „mittel“); the average place of the all-time balance takes only seasons with at least one complete matchday (the others still count as played); a best or worst matchday reached twice lists both; tied record holders appear by season (newest first), matchday, then the overall table; the records page opens on all seasons; a player's name opens their page on the season being viewed. | Product owner, 2026-09-24. |
| D12 | **The kicker import (MD-05) fills the form in the browser; it fetches nothing from kicker.** It reads the text copied from the league page — the „Spieltagswertung“ block, place (a tie reads „–“), name and points per row, up to the next block („Saisonwertung“). The name is compared with the alias without kicker's „(Admin)“ marker, surrounding blanks or case. Pasted points replace what a field held; the other fields stay. The text does not say which matchday it is: it applies to the matchday being edited. Button „Aus kicker einfügen“, confirmation „Übernehmen“. | Product owner, 2026-09-24 (answers Q9: „mach 1 … als Ergänzung“ to the paste route, over a scraper that would store kicker credentials). |
| D13 | **A password can be set from the server:** `php artisan users:set-password --email=… --password=…` sets an existing user's password (validated as any password is) and marks the address verified. `users:create-admin` takes `--email` and `--name`, the prompts remaining for a terminal. Both exist because Laravel Cloud runs commands without a terminal, and because an invitation that never arrives would otherwise lock the first admin out. | Product owner, 2026-09-25. Supplements B14: the invitation stays the way accounts get their password. |

## Still open

Questions the requirements do not answer yet. Code stops at these boundaries and asks.

| Id | Question | Blocks |
| --- | --- | --- |

## Spec notes

Places where the catalogue turned out to be ambiguous or wrong while implementing it, with the
user's resolution. Newest first.

- **2026-09-24 — Punkte aus dem kicker Manager (vormals Q9)** → MD-05, D12: die kopierte
  Liga-Seite einfügen, zusätzlich zur Eingabe von Hand; kein automatischer Abruf. Die Seite, wie
  sie kopiert wird, liegt als Referenz in `tests/Fixtures/kicker-matchday-paste.txt` (Product
  Owner).

- **2026-09-24 — Staffel änderbar, Saisons löschbar, Geld je Spieltag** (Product Owner):
  Startbetrag und Schrittweite darf jeder angemeldete Benutzer jederzeit ändern, die ganze Saison
  wird neu berechnet (SEA-05; die Sperre aus Q2 entfällt). Ein Admin löscht eine Saison
  wiederherstellbar (SEA-06). Geld je Spieltag im Spieltag-Block, als Graph in der Strafenkasse und
  als Rekord (STAT-13–15). ACC-03 und PEN-04 entsprechend angepasst.

- **2026-09-24 — Zwischenabrechnung (vormals Q10)** → PEN-04: einmal je Saison, der Spieltag wird
  beim Anlegen festgelegt und bleibt änderbar; die Abschnitte heißen „Hinrunde“ und „Rückrunde“;
  die Gesamttabelle zeigt Hinrunde, Rückrunde und Gesamt (Product Owner).

- **2026-09-24 — Die Liga-Liste** (`Kicker26_27.xlsx`, Blatt „Spieltagsstrafen“, als Foto): 13
  Mitspieler, Strafen der Spieltage 1–4 mit Startbetrag 5,00 € und Schrittweite 0,50 €. Die Beträge
  folgen PEN-01 genau (je Spieltag 27,50 €, die drei Besten bei 0 €); als Referenz in
  `tests/Fixtures/kicker-26-27-penalties.json`. Das Blatt hält Strafen, keine Punkte: Die Punkte der
  laufenden Saison werden über die App nachgetragen (Product Owner).

- **2026-09-24 — Q1–Q8 beantwortet** (Product Owner: „ok“ zu allen Vorschlägen):
  - Q1: Gleiche Punktesumme in der Gesamttabelle → gleicher Platz.
  - Q2: Startbetrag und Schrittweite sind ab dem ersten eingetragenen Spieltag gesperrt.
  - Q3: Platzierung dicht gezählt: 1, 2, 3, 3, 4 (wie die Strafenstaffel).
  - Q4: Öffentliche Seite: Gesamttabelle mit Punkte- und Strafensumme, Ergebnisse je Spieltag
    (Punkte, Platz, Strafe), Auswahl früherer Saisons.
  - Q5: Eine Saison hat nur eine Bezeichnung, z. B. „2025/26“.
  - Q6: Die Mitspielerliste einer Saison ist ab dem ersten eingetragenen Punkt gesperrt.
  - Q7: Punkte sind ganze Zahlen, negativ erlaubt.
  - Q8: Die Gesamttabelle zählt nur abgeschlossene Spieltage.
  - Außerdem: Die App heißt **Vivalaraza**, die Oberfläche ist **nur Deutsch**. `/` führt ohne
    Anmeldung auf die öffentliche Saisonansicht (als `D1` festzuhalten, ersetzt B12).
  - Eingearbeitet am selben Tag: Q1, Q3, Q8 → STD-01/MD-03; Q2, Q5 → SEA-01; Q4 → ACC-01;
    Q6 → SEA-03; Q7 → MD-01. Dazu (Product Owner): innerhalb eines Platzes der Gesamttabelle steht
    die geringere Strafensumme zuerst; vorausgewählt ist die zuletzt angelegte Saison;
    Mitspieler und Saisons werden nur angelegt (ACC-03). Name und Sprache → D1, D2.

## Superseded

Decisions no longer in force, kept so that a comment citing one still leads somewhere.

| Id | Decision | Replaced by |
| --- | --- | --- |
| B2 | **Auth is Fortify's**, as the starter kit ships it (login, registration, reset, e-mail verification, two-factor, passkeys). A project removes what it does not need (e.g. registration) as a `D*` decision. | B14 — registration removed; accounts are created by admins. |
| B12 | **No landing page.** `/` redirects to the dashboard; a signed-out visitor is sent on to the login. The starter kit's marketing welcome page is removed. | D1 — `/` is the public season view. |
