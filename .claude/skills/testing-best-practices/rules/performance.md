# Suite performance

These are project and CI settings, not per-test choices ([`isolation.md`](isolation.md) covers
those). Measure before changing anything: find the slow test, then fix its cause. Each change here
touches `phpunit.xml`, `tests/Pest.php` or CI — propose it; do not make it in passing.

## Already in place

- `BCRYPT_ROUNDS=4`, `CACHE_STORE=array`, `QUEUE_CONNECTION=sync`, SQLite `:memory:` (`phpunit.xml`).
- Domain tests boot nothing — keeping rules in `app/Domain` is the biggest speed lever there is.

## Worth checking

- Xdebug off (and pcov unless coverage is wanted).
- Packages that do work on every request disabled in testing (Telescope, Pulse, Nightwatch — none
  installed today).
- `withoutVite()` where a feature test would otherwise resolve a built asset.
- Global guards in `tests/Pest.php` — not set today, each a suite-wide proposal:
  `Http::preventStrayRequests()` (catches the HTTP client, not direct Guzzle/cURL),
  `Sleep::fake(syncWithCarbon: true)` so retries do not sleep, `Exceptions::fake()` so nothing
  reports to an external service.

## Parallel

`php artisan test --parallel` (or `./vendor/bin/pest --parallel`, `--processes=N` to override the
count). Each process gets its own database. A test that fails only in parallel breaks one of these:

- it creates every record it reads,
- it does not depend on run order,
- it shares no file, cache key or queue with another test (name such resources per process).

## Run fewer tests

`./vendor/bin/pest --parallel --tia` runs only the tests affected by recent changes and replays
cached results for the rest; Pest 5 detects Laravel and Inertia tests without configuration.
It is a local speed-up — `composer test` and CI still run everything.

## Split across CI jobs

`./vendor/bin/pest --update-shards` records per-test timings in `tests/.pest/shards.json` (commit
it); each CI job runs `./vendor/bin/pest --shard=1/4`, `2/4`, … so shards balance by runtime.

## Find the slow test

`./vendor/bin/pest --profile` lists the slowest tests. Start with the top ten — the same cause
usually applies across the suite (a real sleep, a real HTTP call, an unneeded full-app boot in what
should be a domain test).
