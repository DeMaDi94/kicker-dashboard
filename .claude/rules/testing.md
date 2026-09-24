---
paths:
  - 'tests/**'
  - 'resources/js/**/*.test.ts'
  - 'resources/js/**/*.test.tsx'
---

# Testing approach

Four layers, and the choice between them is not a preference — it follows from where the rule
lives. Most requirements are pure rules, so most tests are fast unit tests.

| Layer | Where | Runner | Tests |
| --- | --- | --- | --- |
| Domain | `tests/Unit/Domain/{Area}/` | Pest | The requirements' rules. No database, no HTTP. |
| Feature | `tests/Feature/{Area}/` | Pest | Routes, validation, Inertia props, persistence. |
| Frontend | `resources/js/**/*.test.tsx` | `vp test` | Interactive primitives, hooks, TS mirrors. |
| End-to-end | `tests/e2e/*.spec.ts` | Playwright | Whole user workflows. |

Plus two things that are not really layers: `tests/Architecture/` (boundary rules and the
translation check, run in the normal suite) and `tests/Fixtures/` (golden vectors and datasets).

**Mirror, do not colocate.** `app/Domain/Billing/InvoiceNumber.php` →
`tests/Unit/Domain/Billing/InvoiceNumberTest.php`. `app/Http/Project/StoreProject/` →
`tests/Feature/Project/StoreProjectTest.php`. The biggest test category is domain rules shared
across areas, which have no single controller to sit beside. (Frontend tests are the exception:
Vitest's convention is `thing.test.tsx` beside `thing.tsx`.)

## Cite the requirement id

Every test that implements a requirement names its id in the description or a comment above the
case. `php artisan spec:coverage` reads those citations and **fails** when something marked `done`
has no test, or when a test cites an id the catalogue does not declare. The Stop gate runs it.

Group by requirement where several cases belong to one rule:

```php
describe('INV-04 · invoice numbers', function () {
    it('starts at 0001 in a new year', function () { … });
    it('never reuses a cancelled number', function () { … });
});
```

A test file that uses requirement-shaped ids as *fixtures* (the spec tooling's own tests) carries
the marker `spec-coverage: ignore` so they do not register as coverage.

## Prefer a golden vector to a hand-written case

Where a rule has a reference answer set — an existing system's output, a spreadsheet the business
already trusts, a regulator's worked examples — freeze it as JSON in `tests/Fixtures/` and assert
against it with a dataset rather than inventing inputs. It is more coverage for less work and it
proves fidelity instead of asserting your own reading:

```php
it('matches the reference rounding', function (array $case) {
    expect(Rounding::of($case['in']))->toBe($case['out']);
})->with(goldenVectors('rounding'));
```

Real inputs beat invented ones.

## Feature tests

Required: **success**, plus **validation** (`assertInvalid()`) wherever an `{Action}Request`
exists, **not found** wherever the route takes a resource id, and **refused** wherever the route
sits behind an authorisation rule.

Assert Inertia properly — the component name and the props, not rendered HTML:

```php
$this->get(route('projects.show', $project))
    ->assertInertia(fn (AssertableInertia $page) => $page
        ->component('project/show')
        ->has('project.tasks', 3));
```

Use the named assertions — `assertOk()`, `assertForbidden()`, `assertNotFound()`,
`assertRedirect()`, `assertInvalid()` — not `assertStatus(…)`; the failure message says what was
expected. Pest's helpers are functions: `use function Pest\Laravel\{actingAs, mock};`. The
`testing-best-practices` skill has the fuller reference.

## End-to-end traps

- `getByLabel` matches a **substring**, so „Name“ also matches a „Delete name“ button. Pass
  `{ exact: true }`.
- A filter bar's selects can share a label with the row fields. Scope row locators to `tbody`.
- An `<img>` whose URL does not resolve has no size, so Playwright calls it hidden. Assert its
  count, not its visibility.
- Pages arrive server-rendered; a fill before hydration is lost. `tests/e2e/support/test.ts`
  wraps `goto`/`reload` to wait for React — import `test` from there, not from `@playwright/test`.
- The suite signs in once (`auth.setup.ts`) because the login route is throttled.
- Locate by the text the user sees in the default locale; the suite runs with `locale: 'de-DE'`
  when the app's default locale is German.
- A failed run leaves rows in the shared dev database. Run `php artisan migrate:fresh --seed`
  before the next run.

## Do NOT

- Do not mock the domain layer. It is pure by construction — call it. Mock only external I/O
  (`Http::fake()`, `Storage::fake()`, `Mail::fake()`).
- Do not assert on UI text you have not read in the requirement or the translation file. Copying
  an expectation out of your own implementation proves nothing.
- Do not use `page.waitForTimeout()` in Playwright. Wait for a condition.
- Do not delete or skip a failing test to get green. Either the code or the test is wrong — say which.
- Do not write a throwaway verification script when a test proves the same thing.
- Do not mark a requirement `done` because its test passes if the rule is not actually wired into
  the app — that is `in-progress`.
