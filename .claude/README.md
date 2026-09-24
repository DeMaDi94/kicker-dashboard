# `.claude/` — shared agent setup

Everything here is committed and applies to every teammate using Claude Code in this repo.
`settings.local.json` is personal and gitignored.

The machinery: path-scoped rules, a Stop gate over changed files, PreToolUse guards, a prompt
review of every edit, and task-shaped skills. It was extracted from a production Laravel + Inertia
app and stripped of that app's domain; what is left is the part that makes Claude build *against a
specification* rather than around one.

## Requirements

- **`jq`** — every hook parses its payload with it. Without `jq` the hooks fail open (they exit 0),
  so nothing breaks and nothing is checked either. `brew install jq`.
- `vendor/` and `node_modules/` installed — the hooks call `vendor/bin/{pint,phpstan,pest}` and
  `node_modules/.bin/{vp,tsc}` directly.

Hooks load at session start. After changing `settings.json`, start a new session.

## Layout

| Path | What it does |
| --- | --- |
| `settings.json` | Permissions and hook wiring. Committed. |
| `hooks/verify-changes.sh` | **Stop gate.** Pint, PHPStan, `vp lint`, `tsc`, `spec:coverage` and the translation check over the working tree; blocks the turn if any fails. |
| `hooks/format-changed-file.sh` | PostToolUse: Pint or `vp fmt` on the file just written. Async, never blocks. Skips `docs/`. |
| `hooks/guard-spec-contract.sh` | PreToolUse (Edit/Write): asks the user before any write to `docs/REQUIREMENTS.md`. |
| `hooks/guard-gate-commands.sh` | PreToolUse (Bash): refuses a PHPStan baseline and `npx`-invoked gates. |
| `hooks/smoke-test.sh` | Verifies all of the above. Run it after editing any hook. |
| `rules/` | Path-scoped guidance, loaded automatically when a matching file is touched. |
| `agents/` | Four subagents: `explorer`, `reviewer`, `gate-fixer`, `test-author`. See below. |
| `skills/` | The task skills plus vendored workflow and stack references. See below. |
| `THIRD-PARTY-NOTICES.md` | Where each vendored skill came from, the upstream version, its licence and what was changed. |

## The rules

| Rule | Applies to |
| --- | --- |
| `requirements.md` | `app/`, `resources/js/`, `tests/` — the spec is the contract, cite ids, never invent behaviour |
| `architecture.md` | `app/`, `routes/`, `database/`, `config/` — backend layers and boundaries |
| `react.md` | `resources/js/`, `resources/css/` — frontend layers, the core primitives, the house style |
| `i18n.md` | `resources/js/`, `resources/views/`, `app/`, `lang/` — one catalogue, English keys, every locale complete |
| `testing.md` | `tests/`, `*.test.ts(x)` — the four layers, requirement citation, golden vectors, e2e traps |
| `php.md` | `**/*.php` — style, camelCase, PHPStan and the no-baseline rule |
| `minimalism.md` | `app/`, `resources/js/` — least code that works |

## Agents

Each is a fresh Claude Code instance with its own context window: it does the reading or the
iterating there and hands back only a report — worth it for anything read-heavy or noisy.

| Agent | For | Tools | Model |
| --- | --- | --- | --- |
| `explorer` | Locating and explaining code. Delegate from ~3 searches up. | read-only | sonnet |
| `reviewer` | Diff review for what the gates cannot see — requirement fidelity, invented values, authorization, props exposure, test honesty. | read-only | opus |
| `gate-fixer` | Making a failing gate green; never games `spec:coverage` or adds a baseline. | read+write | sonnet |
| `test-author` | Tests derived from the requirement, driven to green. | read+write | sonnet |

Two lines keep them cheap and safe, and both are easy to lose when editing: `model:` (only
`reviewer` needs Opus) and `tools:` (the read-only agents cannot edit by accident). They inherit
`CLAUDE.md` and the path-scoped rules, so the agent files **point at** the conventions instead of
restating them — a convention duplicated into an agent file is one more place to forget to update.

### Agent or skill?

Both are markdown under `.claude/`; the difference is where the output lands. A **skill** loads
instructions into the *current* context — you see every step. An **agent** runs in its own window
and returns only the outcome. `testing-best-practices` + `tdd` vs `test-author`, or
`receiving-code-review` after `reviewer`, are the same work at those two distances.

## Skills

| Skill | Kind | For |
| --- | --- | --- |
| `start-project` | task | Once, on a fresh copy |
| `implement-requirement` | task | Requirement ids to done — calls `grilling`, `test-author`, `reviewer` |
| `create-screen` | task | Scaffold an Inertia screen |
| `grilling` | workflow | One question round, each with a recommended answer, before building |
| `domain-modeling` | workflow | Sharpen terms into `docs/GLOSSARY.md`, decisions into `docs/DECISIONS.md` |
| `diagnosing-bugs` | workflow | Feedback loop first, then hypotheses; three failed fixes → question the design |
| `tdd` | workflow | Red–green in vertical slices at the layer the rule lives in |
| `codebase-design` | workflow | Deep-module vocabulary, inside the fixed layers |
| `receiving-code-review` | workflow | Verify review findings against code and spec before acting |
| `finishing-a-development-branch` | workflow | Gates + ledger, then merge / PR / keep |
| `resolving-merge-conflicts` | workflow | Hunk by hunk by intent, then the gates |
| `wizard` | workflow | A bash script that walks a human through steps only they can do |
| `writing-for-agents` | workflow | Editing skills, rules and `CLAUDE.md` |
| `laravel-best-practices` | reference | Eloquent, migrations, queues, caching, HTTP, security — rewritten for this architecture |
| `testing-best-practices` | reference | Pest 5 test design beneath `rules/testing.md` |
| `inertia-react-development` | reference | Inertia 3 client patterns in the house idiom |
| `fortify-development` | reference | Fortify as B14 runs it |
| `laravel-permission-development` | reference | spatie/laravel-permission as B13 runs it |

Vendored skills are **copies**, not installs: they carry `license` and `metadata.source` in their
frontmatter and are listed in `THIRD-PARTY-NOTICES.md` with the upstream tag to re-diff against.
Every one has been rewired to this repo's contract — `docs/GLOSSARY.md` and `docs/DECISIONS.md`
instead of their own context files, this repo's commands, and "a value needs a requirement or
decision id" wherever the upstream text suggests a default. When a vendored text and a rule in
`rules/` disagree, the rule wins; fix the skill.

Short version traps (Inertia 3, Wayfinder, Tailwind 4, PHP 8.4/8.5, Pest) live in the always-loaded
rules rather than a skill — an agent does not know to look them up, and a plain skill measurably
fails to trigger for exactly that kind of guidance.

## Gates nothing else provides

**Requirement traceability.** `php artisan spec:coverage` cross-references `docs/REQUIREMENTS.md`,
`docs/spec/status.txt` and the ids cited in tests. It fails when something marked `done` has no
test citing it, when a `changed`/`wont-do` entry carries no reason, or when a test cites an id the
catalogue does not declare. The Stop gate runs it every turn.

**An invented-behaviour review.** A prompt hook reads each edit under `app/` or `resources/js/`
and fails it for a business rule, default, threshold or option list that is not traceable to a
requirement or decision id, and for user-facing text that bypasses `t()` / `__()`. The first is
the failure mode that matters most when an agent builds from a spec: a plausible invented constant
looks correct and no test contradicts it.

**Translation completeness.** `tests/Architecture/TranslationsTest.php` fails on any literal
`t('…')` / `__('…')` key missing from a locale file. The Stop gate runs that one test file.

## What is deliberately not here

- **A test gate on Stop.** Pest is fast enough to run yourself; Playwright boots a server and takes
  minutes. The block message names the commands instead.
- **A spell-check gate.** UI copy lives in several languages in `lang/`; a single dictionary would
  fight it.
- **Session-start or per-prompt skill injection** (superpowers' `using-superpowers`, ponytail's
  hooks). They put the same text into every prompt and every subagent, and the "always check for a
  skill first" ritual competes with `CLAUDE.md`. Path-scoped rules do the same job only where it
  applies.
- **Planning and spec-writing skills** (superpowers' brainstorming / writing-plans /
  executing-plans, mattpocock's to-spec / to-tickets / triage). Plan mode and `implement-requirement`
  cover them, and a second spec document breaks "every rule traces to `REQUIREMENTS.md`".
- **Generic code-review and simplify skills** (ponytail-review, mattpocock `code-review`,
  superpowers `requesting-code-review`). The built-in `/code-review` and `/simplify` plus the
  `reviewer` agent cover them; `rules/minimalism.md` lists what is house shape, not over-engineering.
- **`laravel/boost` as a dependency.** Its guideline text is vendored and adapted instead; the MCP
  tools map onto `artisan` commands (`route:list`, `tinker --execute`, `db:table`, `pail`).
- **A naming-review prompt hook.** The code follows Laravel's own conventions, so Pint and PHPStan
  cover it — and `docs/GLOSSARY.md` fixes the domain vocabulary, which is the part that would
  actually drift.

## Debugging a hook

```sh
bash -n .claude/hooks/<script>.sh    # syntax — after every edit
bash .claude/hooks/smoke-test.sh     # behaviour, all hooks, plus a real Stop-gate run
jq -e '.hooks' .claude/settings.json # malformed JSON silently disables everything
```

One caveat worth knowing: `guard-gate-commands.sh` anchors its match to command position, so the
refused flags can be written inside backticks in prose. Written as flowing text in a heredoc they
still trip it — which is why `smoke-test.sh` is a file rather than a command you type.
