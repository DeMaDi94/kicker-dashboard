# Third-party notices

Some skills and rule passages in `.claude/` are adapted from MIT-licensed projects. They are copies,
not installs — nothing here needs a plugin or a package. To update one, diff the upstream version
named below against the next release and carry the harness changes over.

## Sources

| Upstream | Version | Copyright | Used in |
| --- | --- | --- | --- |
| [mattpocock/skills](https://github.com/mattpocock/skills) | 1.2.2 | Copyright (c) 2026 Matt Pocock | `grilling`, `domain-modeling`, `diagnosing-bugs`, `tdd`, `codebase-design`, `resolving-merge-conflicts`, `wizard`, `writing-for-agents` |
| [obra/superpowers](https://github.com/obra/superpowers) | v6.4.1 | Copyright (c) 2025 Jesse Vincent | `receiving-code-review`, `finishing-a-development-branch`, the three-failed-fixes rule in `diagnosing-bugs` |
| [laravel/boost](https://github.com/laravel/boost) | v2.10.0 | Copyright (c) Taylor Otwell | `laravel-best-practices`, `testing-best-practices`; the PHP 8.4/8.5 and Tailwind 4 lines in `rules/php.md` and `rules/react.md` |
| [@inertiajs/react](https://github.com/inertiajs/inertia) | 3.7.1 | Copyright (c) Jonathan Reinink | `inertia-react-development` |
| [inertiajs/inertia-laravel](https://github.com/inertiajs/inertia-laravel) | v3.3.4 | Copyright (c) Jonathan Reinink | the Inertia 3 lines in `rules/react.md` |
| [laravel/fortify](https://github.com/laravel/fortify) | v1.40.0 | Copyright (c) Taylor Otwell | `fortify-development` |
| [laravel/wayfinder](https://github.com/laravel/wayfinder) | v0.1.21 | Copyright (c) Taylor Otwell | the Wayfinder lines in `rules/react.md` |
| [spatie/laravel-permission](https://github.com/spatie/laravel-permission) | 8.3.0 | Copyright (c) Spatie bvba | `laravel-permission-development` |
| talentboard-backend / -frontend (internal) | — | ZFM | `agents/explorer`, `reviewer`, `gate-fixer`, `test-author` |

The package-shipped texts (Inertia, Fortify, Wayfinder, spatie) come from each package's
`resources/boost/` folder, in `vendor/` or `node_modules/`.

## Changes against upstream

Common to every vendored skill: frontmatter gains `license` and `metadata.source`; references to the
upstream project's own context files (`CONTEXT.md`, ADR folders, issue trackers, setup skills) point
at `docs/GLOSSARY.md`, `docs/DECISIONS.md` and `docs/REQUIREMENTS.md` instead; Boost's Blade
templating is flattened to this stack's branch and its MCP tools are replaced by `artisan` commands;
cross-links go only to skills that exist here, unprefixed.

- **grilling** — frontmatter only.
- **domain-modeling** — terms go to the `docs/GLOSSARY.md` tables (`GLOSSARY-FORMAT.md` replaces
  `CONTEXT-FORMAT.md`), decisions to a D-row or a „Still open“ row in `docs/DECISIONS.md`, gated by
  the ADR format's three criteria; areas are never invented; `REQUIREMENTS.md` is only offered wording.
- **diagnosing-bugs** — this repo's feedback loops, fastest first; the regression test cites the
  requirement id; after three failed fixes, stop and question the design (from superpowers'
  systematic-debugging); no architecture hand-off.
- **tdd** — seams follow the four layers in `rules/testing.md`; tests cite ids; golden vectors
  preferred; Pest examples (one Vitest); mock only external I/O through Laravel's fakes.
- **codebase-design** — the layers of B4 are fixed and enforced; the vocabulary works inside them; a
  seam's "port" is distinguished from this repo's `Ports/` classes.
- **resolving-merge-conflicts** — this repo's checks; rules for `status.txt`, `lang/*.json`,
  `REQUIREMENTS.md` and the generated `COVERAGE.md`.
- **wizard** — scoping reads `config/` and CI, never `.env*`; only the human's run writes `.env`.
- **writing-for-agents** — notes that this repo uses `CLAUDE.md` + `.claude/rules/`, not `AGENTS.md`.
- **receiving-code-review** — the spec outranks reviewer suggestions (a new section); this repo's
  test commands per fix; "your human partner" → "the user".
- **finishing-a-development-branch** — Step 1 runs the harness gates and checks the status ledger
  and decisions; Claude Code worktrees (`EnterWorktree`/`ExitWorktree`); no commit on the agent's
  own initiative; the PR body names the requirement ids moved.
- **laravel-best-practices** — rewritten for `Http/{Area}/{Action}` + Service + Port; every TTL,
  retry, timeout, queue or ordering needs a requirement or decision id; authorization by permission
  (B13); FormRequest always; no Action classes, interfaces-for-ports, resource controllers, reflexive
  `cascadeOnDelete()` or mirrored `$attributes` defaults; `blade-views` dropped.
- **testing-best-practices** — Pest 5 only; PHPUnit, Dusk and Pest-browser removed; tenant cases
  generalised; domain-first layer choice; requirement-id citations, golden vectors, `admin()` /
  `member()`, named assertions, no deleting failing tests.
- **inertia-react-development** — every snippet in the house idiom (Wayfinder, `t()`, design tokens,
  typed props, the Users screens as examples); polling via `useLivePoll` (B9), list state via
  `useListQuery`; `useHttp` only for non-page JSON (B1); `setLayoutProps` documented as exported
  (upstream names a `useLayoutProps` hook that does not exist); instant visits need an explicit
  `component`.
- **fortify-development** — scoped to B14: no registration, SPA/Sanctum or `CreateNewUser`
  guidance; features only by decision; routes via `php artisan route:list --only-vendor`.
- **laravel-permission-development** — scoped to B13: permissions checked, never roles; enums in
  `app/Domain/Users`; roles and permissions by migration; names only from a requirement or decision;
  imports instead of FQCNs; no Blade directives, direct permissions, super-admin, teams, wildcards
  or events.

## MIT License

Applies to each upstream above, with its copyright line from the table.

```text
Permission is hereby granted, free of charge, to any person obtaining a copy of this software and
associated documentation files (the "Software"), to deal in the Software without restriction,
including without limitation the rights to use, copy, modify, merge, publish, distribute,
sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or
substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT
NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES
OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
```
