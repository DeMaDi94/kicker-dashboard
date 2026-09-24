# Caching

A TTL, a stale window or a cache key's scope is a decision — cite it. The default store is
`database` (`config/cache.php`) and tests use `array` (`phpunit.xml`); check what the store
supports before relying on tags or locks.

## Use `Cache::remember()` for cache-aside reads

`remember()` is a cache-aside read without a truthiness check. It does not stop concurrent requests
computing the same missing value — use a lock when duplicate computation must be prevented.

The manual version treats valid falsy values (`false`, `0`) as misses:

```php
$stats = Cache::get('stats');
if (! $stats) {
    $stats = $this->computeStats();
    Cache::put('stats', $stats, 60);
}
```

Correct:

```php
$stats = Cache::remember('stats', 60, fn () => $this->computeStats());
```

## Consider `Cache::flexible()` for stale-while-revalidate

For frequently read keys, `flexible()` serves stale data during a stale period and registers a
deferred refresh — during a request it runs after the response, in the same process; it is not a
durable job. After the stale period the request recomputes synchronously.

```php
Cache::flexible('users', [300, 600], fn () => User::all());
```

Fresh for five minutes, may be served stale until ten.

## Use `Cache::memo()` within one execution

When the same key is read repeatedly in one request or job, `memo()` keeps resolved values in
memory for that execution; writes through the memoized store update it.

```php
$settings = Cache::memo()->get('settings');
```

## Use cache tags to invalidate related groups

Tags group entries for invalidation. They are **not** supported by the `file`, `dynamodb` or
`database` drivers — so not by this repo's default store. Tags need a store change, which is a
decision.

```php
Cache::tags(['user-1'])->flush();
```

## Use `Cache::add()` for atomic conditional writes

`add()` writes only when the key does not exist, atomically.

```php
// Racy
if (! Cache::has('lock')) {
    Cache::put('lock', true, 10);
}

// Atomic
Cache::add('lock', true, 10);
```

Use `Cache::lock()` rather than an ordinary key when lock ownership and safe release matter.

## Use `once()` for in-process memoization

`once()` memoizes a callback's result for the current request or job, scoped to the object instance
when called from one. Unlike `Cache::memo()` it never touches a store.

```php
/**
 * @return Collection<int, Role>
 */
public function roles(): Collection
{
    return once(fn () => $this->loadRoles());
}
```

## Failover stores in production

The `failover` driver tries each configured store in order when an operation *throws*. It does not
consult later stores on an ordinary miss, and data is not replicated between them.

```php
'failover' => ['driver' => 'failover', 'stores' => ['redis', 'database']],
```

## Cached values are never the source of an editable setting

A value the team edits from a screen is data (`.claude/rules/architecture.md` → Configuration).
Caching it is fine; the cache is invalidated when the setting is saved, in the same service.
