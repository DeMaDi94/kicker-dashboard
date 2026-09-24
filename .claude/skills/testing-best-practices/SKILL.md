---
name: testing-best-practices
description: Pest 5 test design and review for this repo — choosing the layer (domain unit, feature, frontend, e2e), what to cover, naming and grouping by requirement id, assertions, factories and datasets, golden vectors, fakes and mocks, time and HTTP isolation, endpoint and security coverage, suite speed. Load when writing, extending or reviewing a PHP test under tests/, or deciding which tests a change needs. Playwright traps and the layer table live in .claude/rules/testing.md; this skill is the how-to beneath it.
license: MIT
metadata:
  source: "laravel/boost@v2.10.0 .ai/laravel/skill/testing-best-practices"
  adapted: "Flattened to the Pest 5 branch; PHPUnit, Dusk and Pest-browser sections dropped (e2e is Playwright); tenant sections generalised to record ownership and permissions (B13); 'feature test first' replaced by the harness's domain-first layer choice; added requirement-id citations, mirrored layout, goldenVectors(), UI-text and never-delete-a-failing-test rules; Boost MCP tools replaced by vendor source and artisan."
---

# Testing best practices

`.claude/rules/testing.md` and `.claude/rules/requirements.md` override every rule here. Read them
first: the four layers, mirroring, requirement citations, golden vectors, the required feature-test
cases and the Playwright traps are defined there and not repeated.

## What to test

- **Choose the layer by where the rule lives.** Most requirements are pure rules in
  `app/Domain/{Area}/`, so most tests are fast unit tests in `tests/Unit/Domain/{Area}/` — no
  database, no HTTP, no booted app. A feature test in `tests/Feature/{Area}/` proves the route,
  validation, authorization, Inertia props and persistence, and needs only one case per rule to
  show the wiring; the matrix lives in the domain test. Frontend behaviour is `vp test`; a whole
  workflow is Playwright.
- **Every test that implements a requirement cites its id** — in `describe('ABC-01 · …')`, the
  `it()` text, or a comment above the case. `php artisan spec:coverage` reads it.
- **Mirror the app.** `app/Domain/Billing/InvoiceNumber.php` →
  `tests/Unit/Domain/Billing/InvoiceNumberTest.php`; `app/Http/Project/StoreProject/` →
  `tests/Feature/Project/StoreProjectTest.php`.
- Test observable behaviour and contracts. A test must still pass after an implementation change
  that keeps the behaviour.
- Cover every decision the change adds: each branch, validation, calculation and authorization.
- **Expected values come from the requirement, a golden vector or the translation catalogue** —
  never from the implementation, and never UI text copied out of your own code.
- Leave the framework to its own tests. What this project *configures* (a constrained relation, a
  cast, a scope, a rule) is ours to test.
- Keep every test that detects a distinct defect. When two tests catch the same defect, shrink the
  higher-layer one to the single case that proves the wiring, and say so.
- **Never delete, skip or weaken a failing test to get green.** A red test is either a bug in the
  code or a question about the requirement — fix the code or ask.
- Use the tools already installed (Pest 5, `pest-plugin-laravel`, Mockery, Playwright). A new test
  dependency needs approval.
- Architecture tests (`tests/Architecture/`) are judged by the convention they protect, not by the
  rules above: they check declarations on purpose.

## How to apply

1. Read the code under test, the requirement it implements, and the tests beside where the new test
   will live. List every decision in the code.
2. Pick the rule files below that apply and read them.
3. Report a defect you find before testing around it — a write action with no `{Action}Request`, a
   route with no permission check, a rule with no id. Test the actual behaviour and tell the user.
4. Write the tests. Run the narrowest set first:
   `./vendor/bin/pest --filter=InvoiceNumberTest` or `php artisan test tests/Unit/Domain/Billing`.
   Then `composer test`, and `php artisan spec:coverage` if a ledger status changed.
5. Walk `rules/review.md` over what you wrote.

## Rule index

| Subject | Rule file |
| --- | --- |
| Pest and Laravel features that already do the work | [`rules/finding-features.md`](rules/finding-features.md) |
| Test names, `it()`/`test()`, grouping by requirement | [`rules/naming.md`](rules/naming.md) |
| Arrange-act-assert, choosing the assertion, known values, complete results | [`rules/assertions.md`](rules/assertions.md) |
| Endpoint coverage, authorization by permission, validation, ownership | [`rules/endpoint-tests.md`](rules/endpoint-tests.md) |
| Factories, `admin()`/`member()`, datasets, golden vectors | [`rules/test-data.md`](rules/test-data.md) |
| Fakes, mocks, outbound HTTP, time, randomness, the database | [`rules/isolation.md`](rules/isolation.md) |
| Escaping, injection through sort/filter, other users' records, privilege | [`rules/security.md`](rules/security.md) |
| Suite speed: environment, parallel, TIA, shards, profiling | [`rules/performance.md`](rules/performance.md) |
| Reviewing a test or a suite | [`rules/review.md`](rules/review.md) |
