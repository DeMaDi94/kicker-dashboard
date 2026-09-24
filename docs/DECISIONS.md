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
| B12 | **No landing page.** `/` redirects to the dashboard; a signed-out visitor is sent on to the login. The starter kit's marketing welcome page is removed. | Internal products open on their work. A product with a public front page adds it as a `D*` decision. |
| B13 | **Roles and permissions through `spatie/laravel-permission`.** A user holds exactly one role — `admin` or `user` (`App\Domain\Users\Role`). Code checks permissions (`users.view`, `users.create`, `users.update`, `users.delete`; restoring needs `users.delete`), never a role name; the admin role holds all four, the user role none. Roles and permissions are created by a migration, so every environment has them. The signed-in user's permissions are shared as `auth.permissions`. | A project adds a role or a permission without touching the checks. |
| B14 | **Auth is Fortify's without self-registration** (login, reset, e-mail verification, two-factor, passkeys). Accounts are created by an admin (name, address, role); the new user receives an invitation mail with a password-reset token and sets their password on Fortify's reset page — the link expires as reset links do (`config/auth.php`). Setting a password through an emailed link marks the address verified. The first admin of an installation comes from `php artisan users:create-admin`. Addresses are stored lowercased, as Fortify signs in with them. | Replaces B2. Internal products know their users; a product with public sign-up re-enables registration as a `D*` decision. |
| B15 | **Users are soft-deleted**, by an admin from the user list or by a user from their profile. A deleted user is signed out on their next request, cannot sign in (password or passkey) and can be viewed and restored by an admin — there is no permanent delete in the UI. A deleted user's address stays taken; a new account cannot reuse it. Guard rails (`App\Domain\Users\AccountGuard`): an admin cannot delete their own account from the list, cannot remove the admin role from themselves, and the last active admin can neither be deleted (from the list or the profile) nor lose the admin role. | Deleting is reversible, and an installation cannot lock itself out. |
| B16 | **The user list** is a settings section („Benutzer“), visible only with `users.view`. Columns name, email, role and email verified (the date, or „Nicht bestätigt“), every one sortable; it opens sorted by name A–Z. Search over name and email, a role filter, and a filter showing the deleted users instead of the active ones; 20 users per page. An admin edits a user's name and role (not the address) and can send the standard password-reset mail. | |

### Project

| Id | Decision | Why |
| --- | --- | --- |
| | _none yet_ | |

## Still open

Questions the requirements do not answer yet. Code stops at these boundaries and asks.

| Id | Question | Blocks |
| --- | --- | --- |
| | _none yet_ | |

## Spec notes

Places where the catalogue turned out to be ambiguous or wrong while implementing it, with the
user's resolution. Newest first.

## Superseded

Decisions no longer in force, kept so that a comment citing one still leads somewhere.

| Id | Decision | Replaced by |
| --- | --- | --- |
| B2 | **Auth is Fortify's**, as the starter kit ships it (login, registration, reset, e-mail verification, two-factor, passkeys). A project removes what it does not need (e.g. registration) as a `D*` decision. | B14 — registration removed; accounts are created by admins. |
