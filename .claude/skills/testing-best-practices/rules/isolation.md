# Fakes, mocks and determinism

A test that depends on real time, randomness, sleeping or the network fails for reasons unrelated
to the code. Control all four.

## Which double

Pick the first option that applies:

1. **The domain layer: call it.** It is pure by construction — never mock `app/Domain`.
2. **A framework fake** for facades: events, queues, mail, notifications, storage, the HTTP client,
   time, sleep, exceptions. A fake keeps the real code path.
3. **A fake implementation** the application already provides.
4. **A mock** of a container-resolved class only when the real one leaves the process or is
   non-deterministic.
5. **The real thing** for everything else, the database included.

Fake and mock names are in `vendor/laravel/framework/src/Illuminate/Support/Testing/Fakes/` and
`.../Foundation/Testing/Concerns/InteractsWithContainer.php`; confirm before use.

## Framework fakes

- Create each fake inside the test that needs it, not in a file-level `beforeEach()`.
- Pass class names to `Event::fake([...])` and `Queue::fake([...])` when you know what the code
  dispatches. A fake without names can hide an unexpected dispatch — use it only when the test also
  asserts the complete result (`assertNothingPushed()` and friends).
- One assertion per fake: dispatched, or not dispatched. Assert a job's or event's data when the
  data is part of the behaviour.
- `Exceptions::fake()` to assert the right exception is reported. Not `withoutExceptionHandling()`,
  which changes the response under test.
- Create prerequisite factory records **before** `Event::fake()`: a nameless fake also suppresses
  model events a factory relies on. Fake first only when a model event is itself under test, and
  name it.

## Mocking

Import the helper: `use function Pest\Laravel\mock;`. It binds the mock into the container.

```php
use function Pest\Laravel\mock;

mock(ExchangeRateClient::class)
    ->shouldReceive('rateFor')
    ->once()
    ->with('EUR', 'USD')
    ->andReturn('1.0823');
```

`shouldReceive()` before the action declares an expectation; `shouldHaveReceived()` after it for a
spy. `Mockery::on()` or `withArgs()` when equality cannot express the argument (one field of a value
object). Never mock a Port or a service of the area under test to make a feature test pass — that
tests the mock.

## Outbound HTTP

`Http::preventStrayRequests()` so any request without a matching fake fails without reaching the
network. Fake the exact endpoint each test uses; `Http::fake()` with no pattern accepts anything and
hides defects. Test the degraded path too — a failed lookup must never become an error the user has
to resolve (`.claude/rules/architecture.md` → External services).

## Time and randomness

- Freeze or move time in every test that depends on a date, a period or a timestamp:
  `freezeTime()`, `travelTo()`, `travel()`, `travelBack()`. Never `Carbon::setTestNow()`.
- Domain rules take the date as an argument rather than calling `now()`, so their unit tests need no
  time helper at all.
- `Str::createRandomStringsUsing()` when the test asserts a generated string.
- `Sleep::fake()` instead of real sleeping, and assert the sleeps requested.
- Restore time and randomness after each test if the suite does not already.

## The database

- Domain tests (`tests/Unit/Domain`) never touch it. `tests/Pest.php` applies `RefreshDatabase` to
  `tests/Feature` only; the tests run on SQLite `:memory:` (B3).
- `LazilyRefreshDatabase` would skip migrations for feature tests that never query. Switching is a
  suite-wide change — propose it, do not make it in passing.
- Run real queries against real records. Never mock the query builder.
- Where the serialized shape is a contract (an Inertia props shape), assert its exact keys so a new
  attribute leaking out fails the test.
- Test behaviour that comes from the schema (the children a delete removes — if a requirement says
  it does), not the engine's cascade implementation.
