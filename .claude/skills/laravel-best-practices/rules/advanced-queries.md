# Advanced queries

## Select single relationship values with subqueries

When only one value from a has-many relationship is needed, consider a correlated subquery with
`addSelect()` instead of loading the whole relationship.

```php
/**
 * @param  Builder<User>  $query
 */
#[Scope]
protected function withLastLoginAt(Builder $query): void
{
    $query->addSelect([
        'last_login_at' => Login::select('created_at')
            ->whereColumn('user_id', 'users.id')
            ->latest()
            ->take(1),
    ])->withCasts(['last_login_at' => 'datetime']);
}
```

(`latest()` here picks *which* login is "last" — it is the definition of the value, not a list
ordering.)

## Dynamic relationships with a subquery foreign key

The same pattern can select a foreign key and expose the model through a `belongsTo`. Eager
loading it still runs one separate query, but avoids loading the whole has-many collection.

```php
/**
 * @return BelongsTo<Login, $this>
 */
public function lastLogin(): BelongsTo
{
    return $this->belongsTo(Login::class, 'last_login_id');
}

/**
 * @param  Builder<User>  $query
 */
#[Scope]
protected function withLastLogin(Builder $query): void
{
    $query->addSelect([
        'last_login_id' => Login::select('id')
            ->whereColumn('user_id', 'users.id')
            ->latest()
            ->take(1),
    ])->with('lastLogin');
}
```

## Combine related counts with conditional aggregates

Combine several counts over the same filtered set into one query with conditional aggregates. Use
`toBase()` when only scalars are needed. Confirm the expression syntax on both SQLite
(development, B3) and the production engine.

```php
$statuses = Feature::toBase()
    ->selectRaw("count(case when status = 'requested' then 1 end) as requested")
    ->selectRaw("count(case when status = 'planned' then 1 end) as planned")
    ->selectRaw("count(case when status = 'completed' then 1 end) as completed")
    ->first();
```

The literals are the enum's stored values (the wire format), never its labels.

## Reuse loaded parent models with `setRelation()`

When a parent and its children are loaded and code also reads `$child->parent`, set the inverse
relationship to the existing instance to avoid a lazy query per child.

```php
$feature->load('comments.user');
$feature->comments->each->setRelation('feature', $feature);
```

## Compare `whereHas()` with an `IN` subquery

`whereHas()` usually produces `EXISTS`; `whereIn()` with a subquery produces `IN`. Either may be
faster depending on engine, indexes, cardinality and plan — measure with representative data.
Neither loads its result set into PHP.

```php
$query->whereHas('company', fn (Builder $q) => $q->where('name', 'like', $term));

$query->whereIn('company_id', Company::where('name', 'like', $term)->select('id'));
```

## Measure two simple queries against one complex one

Two targeted queries can beat one correlated subquery or join when the first is highly selective.
They add a round trip, may transfer a large id list, and give no single-query snapshot. Decide from
query plans and production-like measurements.

## Design composite indexes for the query

For common multi-column sorts, a composite index whose column order supports the filters and the
ordering may help. Matching the `ORDER BY` alone does not guarantee the index is used — verify the
plan.

```php
// Migration
$table->index(['last_name', 'first_name']);

// A query this index may support
User::query()->orderBy('last_name')->orderBy('first_name')->paginate();
```

Which order a list opens in is a requirement or a decision (B16 fixes the user list's), never a
performance choice.

## Correlated subquery for has-many ordering

When sorting by one value from a has-many, a direct join can duplicate parent rows unless it first
reduces the related table to one row per parent. A correlated subquery in `orderBy()` is often
simpler; its performance depends on the plan and indexes.

```php
/**
 * @param  Builder<User>  $query
 */
#[Scope]
protected function orderByLastLogin(Builder $query, string $direction): void
{
    $query->orderBy(Login::select('created_at')
        ->whereColumn('user_id', 'users.id')
        ->latest()
        ->take(1), $direction);
}
```

The direction comes from the caller — the list's sort state — and must be allow-listed before it
reaches the query (see [`security.md`](security.md)).
