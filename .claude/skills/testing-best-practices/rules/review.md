# Reviewing tests

Check every item. A passing test can still be worth nothing — for each test, name the defect it
would catch. Report findings; do not delete or rewrite a test without the user's approval. A
pattern repeated across the suite is reported once, not per file.

## Traceability

- [ ] Every test that implements a requirement cites its id; `php artisan spec:coverage` is green.
- [ ] No test cites an id the catalogue does not declare.
- [ ] Nothing is marked `done` in `docs/spec/status.txt` whose rule is not wired into the app.

## Layer and value

(Architecture tests state a convention; these items do not apply to them.)

- [ ] The rule is tested where it lives: pure rules in `tests/Unit/Domain/{Area}/`, wiring in
      `tests/Feature/{Area}/`, one case above for a matrix below.
- [ ] The file mirrors the class or action it tests.
- [ ] Each test covers observable behaviour and survives an implementation change that keeps it.
- [ ] Declarations are exercised through behaviour; no test re-proves the framework.
- [ ] Each test catches a distinct defect.
- [ ] Every decision the change added has coverage.

## Names and structure

- [ ] Names state a result and its condition; `describe()` names the requirement.
- [ ] One declaration style (`it()`/`test()`) per file.

## Coverage

- [ ] Feature tests cover success, validation, not found and refused where they apply
      (`.claude/rules/testing.md`).
- [ ] Refusals test a missing permission, never a role name check (B13).
- [ ] Records restricted to an owner answer another owner with a status that does not confirm
      existence, where the requirement asks for it.
- [ ] Each validation rule has one endpoint case asserting the translated message.
- [ ] Rendered user input and each dynamic query part have a security test.

## Data and determinism

- [ ] Each test creates its own mutable records; `beforeEach()` holds configuration only.
- [ ] Factory states and relationships say what the data means.
- [ ] `make()` only where the database is not needed.
- [ ] Time, randomness, sleep and outbound HTTP are controlled.
- [ ] The domain is called, not mocked.
- [ ] Each test passes alone and in any order.

## Assertions

- [ ] Expected values are known values — from the requirement, a golden vector or the translation
      catalogue — not recomputed with the implementation's logic or copied from its strings.
- [ ] Named assertions (`assertOk`, `assertForbidden`, `assertNotFound`, `assertInvalid`), never
      `assertStatus(…)`.
- [ ] Write tests assert the response, the database and the side effects; failure paths assert none
      happened.
- [ ] Each fake has one assertion and names its classes unless the complete result is asserted.
- [ ] Each `expect()` chain stays on one subject.
- [ ] No test was deleted, skipped or weakened to make the suite green.
