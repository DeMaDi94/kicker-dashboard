# Collections

## Use higher-order messages for simple operations

```php
$users->each(function (User $user): void {
    $user->markAsVip();
});

$users->each->markAsVip();
```

Higher-order messages work for `each`, `map`, `filter`, `sum` and others. Use a closure when
arguments or non-trivial logic make it clearer.

A pipeline that *computes* something the requirements define (a total, a grouping, a ranking) is a
domain rule: it takes plain values in `app/Domain/{Area}/` and the service hands it the data. A
Laravel `Collection` in the domain is fine — `illuminate/collections` needs no booted app — but an
Eloquent collection is not.

## Choose between `cursor()` and `lazy()`

`cursor()` runs one query and hydrates models one at a time, but cannot eager load. The driver's
result buffering can still use a lot of memory for very large results. Use it for low-memory,
attribute-only iteration when one long-running query is acceptable.

`lazy()` runs chunked queries and returns a flat `LazyCollection`. It supports eager loading per
chunk and does not hold one cursor open for the whole iteration.

```php
User::with('roles')->lazy()->each(function (User $user): void {
    // roles for this chunk are loaded
});

User::cursor()->each(function (User $user): void {
    // attributes only
});
```

## Use `lazyById()` when updating while iterating

`lazy()` uses offset pagination, so updates to columns in the query can shift rows — records get
skipped or processed twice. `lazyById()` paginates by a monotonic key. Do not change that key while
iterating.

## Use `toQuery()` for bulk operations

```php
User::whereIn('id', $users->modelKeys())->update(['active' => false]);

$users->toQuery()->update(['active' => false]);
```

`toQuery()` needs a non-empty Eloquent collection of one model type. Like any bulk update it fires
no per-model events — so no observers, no audit entries written from model events. If the
requirement wants an audit entry per row, iterate.

## Use `#[CollectedBy]` for custom collection classes

```php
#[CollectedBy(UserCollection::class)]
final class User extends Model {}
```
