---
name: tdd
description: Test-driven development. Use when the user wants to build features or fix bugs test-first, mentions "red-green-refactor", or wants integration tests.
license: MIT
metadata:
  source: mattpocock/skills@1.2.2 skills/engineering/tdd
  adapted: "GLOSSARY instead of CONTEXT; seams follow the four test layers of .claude/rules/testing.md; tests cite the requirement id and prefer golden vectors; examples rewritten in Pest; code-review pointer removed."
---

# Test-Driven Development

TDD is the red → green loop. This skill is the reference that makes that loop produce tests worth keeping: what a good test is, where tests go, the anti-patterns, and the rules of the loop. Every section applies on every cycle — consult them before and during the loop, not after.

When exploring the codebase, read `docs/GLOSSARY.md` so test names and interface vocabulary match the project's domain language, and respect the decisions in `docs/DECISIONS.md` in the area you're touching.

## What a good test is

Tests verify behavior through public interfaces, not implementation details. Code can change entirely; tests shouldn't. A good test reads like a specification — "INV-04 · starts at 0001 in a new year" tells you exactly what capability exists and which requirement demands it — and survives refactors because it doesn't care about internal structure.

**Every test cites its requirement id** in the `describe`/`it` description or a comment above the case. `php artisan spec:coverage` reads those citations; a test with no id behind it is testing a rule nobody asked for.

**Expected values come from the spec, not from you.** Where a reference answer set exists, freeze it as JSON in `tests/Fixtures/` and run the rule against it with the `goldenVectors()` dataset helper — real inputs beat invented ones.

See [tests.md](tests.md) for examples and [mocking.md](mocking.md) for mocking guidelines.

## Seams — where tests go

A **seam** is the public boundary you test at: the interface where you observe behavior without reaching inside. Tests live at seams, never against internals.

In this repo the seam is not a free choice — it follows from where the rule lives (`.claude/rules/testing.md`):

| Rule lives in | Seam | Test | Run |
| --- | --- | --- | --- |
| `app/Domain/{Area}/` | the value object / pure function | `tests/Unit/Domain/{Area}/` | `./vendor/bin/pest --filter=…` |
| `app/Http/{Area}/{Action}/` | the route: validation, Inertia props, persistence | `tests/Feature/{Area}/` | `./vendor/bin/pest --filter=…` |
| `resources/js/` | the primitive, hook or TS mirror | `*.test.tsx` beside it | `npm run test -- …` |
| a whole workflow | the browser | `tests/e2e/*.spec.ts` | `npm run test:e2e` |

Most requirements are pure rules, so most tests are domain tests. PHP tests mirror the app structure; they are not colocated.

**Test only at pre-agreed seams.** Before writing any test, write down the seams under test and confirm them with the user. No test is written at an unconfirmed seam. You can't test everything — agreeing the seams up front is how testing effort lands on the critical paths and complex logic instead of every edge case.

Ask: "What's the public interface, and which seams should we test?"

When the shape of that interface is itself in question — how deep the module is, where the seam belongs, what the interface should expose — use the `codebase-design` skill for the vocabulary. It is the shared source of the module, interface, depth, seam, adapter, leverage and locality terms, and it is a reference to consult, not a session to run.

## Anti-patterns

- **Implementation-coupled** — mocks internal collaborators, tests private methods, or verifies through a side channel (querying the database instead of using the interface). The tell: the test breaks when you refactor but behavior hasn't changed.
- **Tautological** — the assertion recomputes the expected value the way the code does (`expect(add($a, $b))->toBe($a + $b)`, a snapshot derived by hand the same way, a constant asserted equal to itself, UI text copied out of your own implementation), so it passes by construction and can never disagree with the code. Expected values must come from an independent source of truth — a known-good literal, a golden vector, the requirement's own wording.
- **Horizontal slicing** — writing all tests first, then all implementation. Bulk tests verify _imagined_ behavior: you test the _shape_ of things rather than user-facing behavior, the tests go insensitive to real changes, and you commit to test structure before understanding the implementation. Work in **vertical slices** instead — one test → one implementation → repeat, each test a **tracer bullet** that responds to what the last cycle taught you.

## Rules of the loop

- **Red before green.** Write the failing test first, then only enough code to pass it. Don't anticipate future tests or add speculative features.
- **One slice at a time.** One seam, one test, one minimal implementation per cycle.
- **Refactoring is not part of the loop.** It belongs to a separate review pass, not the red → green implementation cycle.
