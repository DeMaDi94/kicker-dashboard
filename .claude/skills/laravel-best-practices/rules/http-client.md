# HTTP client

Third-party APIs are called server-side only, through a small client in the owning area that
caches, rate-limits, sends an identifying User-Agent where the provider expects one, and degrades
gracefully when the provider is unreachable (`.claude/rules/architecture.md` → External services).
When another area needs that client, it goes through the owning area's Port. The timeouts and
retry delays below show the API; the values for a real provider need a decision id.

## Set explicit timeouts

The default response timeout is 30 seconds. Choose response and connection timeouts that fit the
service and the calling request or job; retries multiply the total elapsed time.

```php
$response = Http::connectTimeout(3)
    ->timeout(5)
    ->get('https://api.example.com/users');
```

Put shared settings in the area's client (or a macro it registers):

```php
Http::macro('github', function (): PendingRequest {
    return Http::baseUrl('https://api.github.com')
        ->connectTimeout(3)
        ->timeout(10)
        ->withUserAgent(config('app.name'))
        ->withToken(config('services.github.token'));
});

$response = Http::github()->get('/repos/laravel/framework');
```

## Retry only safe operations

Retry transient connection failures, rate-limit responses and server errors with a delay. Retry
idempotent requests such as `GET`. Retry a state-changing request only when the remote API supports
an idempotency key or equivalent duplicate protection.

Unsafe without an idempotency guarantee:

```php
Http::retry([100, 500, 1000])->post('https://api.example.com/v1/charges', $data);
```

Safe for an idempotent request:

```php
$response = Http::connectTimeout(3)
    ->timeout(10)
    ->retry([100, 500, 1000], 0, function (Throwable $exception): bool {
        return $exception instanceof ConnectionException
            || ($exception instanceof RequestException
                && ($exception->response->serverError() || $exception->response->status() === 429));
    })
    ->get('https://api.example.com/data');
```

For a supported state-changing API, send a stable idempotency key on every attempt:

```php
$response = Http::withHeaders(['Idempotency-Key' => $paymentAttempt->uuid])
    ->connectTimeout(3)
    ->timeout(10)
    ->retry([100, 500, 1000], 0, $isTransient)
    ->post('https://api.example.com/v1/charges', $data);
```

## Handle errors explicitly

The client returns `4xx`/`5xx` responses instead of throwing. Inspect the expected statuses or call
`throw()` before consuming a success payload.

```php
$user = Http::connectTimeout(3)
    ->timeout(5)
    ->get('https://api.example.com/users/1')
    ->throw()
    ->json();
```

Graceful degradation — the house rule for lookups — handles the alternatives and returns something
the caller can render without an error:

```php
$response = Http::connectTimeout(3)
    ->timeout(5)
    ->get('https://api.example.com/users/1');

if ($response->successful()) {
    return $response->json();
}

if ($response->notFound()) {
    return null;
}

report($response->toException());

return null;
```

## Pool independent requests

`Http::pool()` runs independent requests concurrently. It changes execution time, not error
handling — inspect or throw per response.

```php
$responses = Http::pool(fn (Pool $pool) => [
    $pool->as('users')->connectTimeout(3)->timeout(5)->get('https://api.example.com/users'),
    $pool->as('posts')->connectTimeout(3)->timeout(5)->get('https://api.example.com/posts'),
]);

$users = $responses['users']->throw()->json();
```

## Fake HTTP requests in tests

Never hit a real endpoint. `Http::fake()` the exact endpoint and `Http::preventStrayRequests()` so
an unexpected request fails. Test the timeout, connection-failure and error paths the client
handles — the graceful-degradation path is a behaviour, not an afterthought.

```php
it('ABC-01 · syncs a user from the API', function () {
    Http::preventStrayRequests();
    Http::fake([
        'api.example.com/users/1' => Http::response(['name' => 'Jane Doe']),
    ]);

    app(SyncUserService::class)(1);

    Http::assertSent(fn (Request $request) => $request->url() === 'https://api.example.com/users/1');
});

it('ABC-01 · keeps the screen usable when the API is down', function () {
    Http::preventStrayRequests();
    Http::fake(['api.example.com/*' => Http::failedConnection()]);

    expect(app(SyncUserService::class)(1))->toBeNull();
});
```
