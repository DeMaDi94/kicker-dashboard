# When to Mock

Fake at **system boundaries** only — external I/O, through Laravel's own fakes:

- External APIs → `Http::fake()`
- Mail → `Mail::fake()`
- File storage → `Storage::fake()`
- Time → `$this->travelTo(…)`

The database is not a boundary here: feature tests run against SQLite `:memory:` (B3).

Don't mock:

- `app/Domain` — it is pure by construction; call it
- Your own services, Ports or models
- Internal collaborators
- Anything you control

## Designing for Mockability

At system boundaries, design interfaces that are easy to fake:

**1. Accept the dependency, don't build it**

A service takes its client through the constructor, so the test swaps what the container hands it — or better, keeps the real client and fakes the transport underneath with `Http::fake()`:

```php
// Easy to fake: the client comes in, and it calls Http, which Http::fake() intercepts
final class FetchExchangeRateService
{
    public function __construct(private ExchangeRateClient $rates) {}
}

// Hard to fake: a hand-rolled client built inside, with its own transport
$client = new GuzzleClient(['base_uri' => config('services.rates.url')]);
```

**2. Prefer one method per external operation over a generic fetcher**

A small client in the owning area with `rateFor(Currency $from, Currency $to)` beats one `request(string $path, array $options)`: each fake returns one specific shape, the test shows which endpoint it exercises, and there is no conditional logic in test setup.
