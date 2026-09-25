# Stack

What is actually installed. Read this file for what the project runs on rather than assuming
versions — several of the defaults changed with Laravel 13 and Inertia 3.

Created **2026-09-24** by `laravel new --react --pest --database=sqlite` (Laravel Installer 5.31.1)
on macOS / darwin 25.3.0.

## Runtime

| | Version |
| --- | --- |
| PHP | 8.5.7 |
| Node | 22.18.0 |
| Composer | 2.10.2 |
| Database (dev, tests) | SQLite (`:memory:` in tests) — decision B3 |
| Database (prod) | a project decision |

## Server

| Package | Version | Note |
| --- | --- | --- |
| `laravel/framework` | v13.33.0 | Slim skeleton: `bootstrap/app.php`, no `Http/Kernel.php` |
| `inertiajs/inertia-laravel` | v3.3.4 | Inertia **3** — `Inertia::once()`, `Inertia::flash()` |
| `laravel/fortify` | v1.40.0 | Starter-kit auth — decision B2 |
| `spatie/laravel-permission` | v8.3.0 | Roles and permissions — decision B13 |
| `laravel/wayfinder` | v0.1.21 | Generates typed TS route helpers from the PHP routes |
| `pestphp/pest` | v5.2.1 | With `pest-plugin-laravel` and `pest-plugin-drift` |
| `larastan/larastan` | v3.12.2 | Level 7, no baseline — decision B11 |
| `laravel/pint` | v1.32.1 | Laravel preset |

## Client

| Package | Version | Note |
| --- | --- | --- |
| `react` | 19.3.0 | With the React Compiler (`babel-plugin-react-compiler`) |
| `@inertiajs/react` | 3.7.1 | |
| `tailwindcss` | 4.3.3 | CSS-first config: tokens in `resources/css/theme.css` |
| `vite` | 8.3.0 | |
| `vite-plus` | 0.3.0 | See below — replaces Vitest, ESLint and Prettier |
| `typescript` | 5.9.3 | `noUncheckedIndexedAccess` on |
| `@playwright/test` | 1.63.0 | Chromium only |
| `@dnd-kit/core` / `sortable` / `utilities` | 6.3.1 / 10.0.0 / 3.2.2 | `<SortableList>` |
| `date-fns` | 4.4.0 | |
| `recharts` | 3.10.1 | The statistics graphs (STAT-04–06, STAT-09); approved by the product owner, D10 |
| `sonner` | 2.0.8 | Starter kit — behind `toast()` in `components/core` |
| `lucide-react` | 0.475.0 | The only icon set |
| `@testing-library/react` | 16.3.3 | With `jest-dom` and `user-event` |
| `jsdom` | 26.1.0 | |

shadcn/ui is wired through `components.json` (style `new-york`, base colour `neutral`, alias
`@/components/ui`) with 26 components generated. Their colours and radii resolve to the house
tokens through `resources/css/app.css`; do not hand-edit them.

The three faces — **Space Grotesk**, **IBM Plex Mono** and **Silkscreen** — are fetched and
bundled at build time by the Vite font plugin (`laravel-vite-plugin/fonts`), not linked from a CDN,
so the app renders correctly offline.

## Vite+ replaces four tools

The starter kit ships `vite-plus`, whose `vp` binary bundles the dev server, the build, a
Vitest-compatible test runner, a linter (oxlint, type-aware) and a formatter. Vitest, ESLint,
Prettier and their config are therefore **not** installed. Always go through the scripts:

| | |
| --- | --- |
| `npm run check` | `vp check` — format + lint + type check (`--fix` to apply) |
| `npm run types:check` | `tsc --noEmit` |
| `npm run test` | `vp test` — frontend unit tests (jsdom) |
| `npm run test:e2e` | Playwright |

`npx vitest`, `npx eslint` and the like resolve unpinned versions and drop the scripts' flags; a
hook refuses them.

## Conventions set up front

- **Locale** `de` (fallback `en`), **timezone** `Europe/Berlin`, faker `de_DE` — decision B6.
  Supported locales in `config/app.php` → `locales`.
- **Design tokens** live in `resources/css/theme.css` — decision B8.
- **The formatter does not touch `docs/` or Markdown.** `vite.config.ts` ignores `docs/**` and
  `*.md`: the requirement catalogue is laid out by hand.
- **SSR is on** (`config/inertia.php`), so the first paint is server-rendered; e2e tests wait for
  hydration (`tests/e2e/support/test.ts`).

## Commands

| | |
| --- | --- |
| `composer setup` | Install, key, migrate + seed, build — from a fresh clone |
| `composer dev` | Serve + queue + Vite |
| `composer ci:check` | Everything CI runs, except the e2e job |
| `composer test` | Pint, PHPStan, the spec gate, Pest |
| `npm run check` | Format + lint + type check (`vp check`; `--fix` to apply) |
| `npm run test` | Frontend unit tests |
| `npm run test:e2e` | Playwright, boots its own server |
| `php artisan migrate:fresh --seed` | Reset the database |
| `php artisan users:create-admin` | Create an admin and send the invitation — the first one of an installation (B14) |
| `php artisan users:set-password` | Set an existing user's password and mark the address verified, when the invitation cannot arrive (D13) |
