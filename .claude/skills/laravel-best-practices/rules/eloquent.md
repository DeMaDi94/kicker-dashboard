# Eloquent

## Define precise relationship types

Define the relationship that matches the association and declare its return type with generics
(`.claude/rules/architecture.md` → Models).

```php
/**
 * @return HasMany<Comment, $this>
 */
public function comments(): HasMany
{
    return $this->hasMany(Comment::class);
}

/**
 * @return BelongsTo<User, $this>
 */
public function author(): BelongsTo
{
    return $this->belongsTo(User::class, 'user_id');
}
```

## Use local scopes for reusable queries

Extract a constraint used in more than one place into a local scope.

Duplicated:

```php
$active = User::where('verified', true)->whereNotNull('activated_at')->get();
$articles = Article::whereHas('user', function (Builder $q): void {
    $q->where('verified', true)->whereNotNull('activated_at');
})->get();
```

Scoped:

```php
/**
 * @param  Builder<User>  $query
 * @return Builder<User>
 */
#[Scope]
protected function active(Builder $query): Builder
{
    return $query->where('verified', true)->whereNotNull('activated_at');
}

$active = User::active()->get();
$articles = Article::whereHas('user', fn (Builder $q) => $q->active())->get();
```

What "active" *means* is a rule. If a requirement defines it, cite the id on the scope — and if
the same definition is needed outside a query (a status badge, an export), the rule belongs in
`app/Domain/{Area}/` and the scope mirrors it.

## Apply global scopes sparingly

A global scope silently changes every query on the model, which makes debugging hard. Prefer local
scopes; reserve global scopes for genuinely universal constraints such as soft deletes (B15 uses
`SoftDeletes` on `User`).

```php
#[Scope]
protected function published(Builder $query): Builder
{
    return $query->where('published', true);
}

Post::published()->paginate(); // explicit
Post::paginate();               // an admin screen sees all
```

## Define attribute casts

Casts go in a `casts()` method, not a `$casts` property.

```php
/**
 * @return array<string, string>
 */
protected function casts(): array
{
    return [
        'is_active' => 'boolean',
        'metadata' => 'array',
        'total' => 'decimal:2',
        'status' => OrderStatus::class,
    ];
}
```

An enum cast points at the enum in `app/Domain/{Area}/`; its stored value is the wire format.

## Cast date and time attributes

Cast a date or timestamp column when code should treat it as a Carbon instance.
`created_at`/`updated_at` are cast already.

```php
protected function casts(): array
{
    return ['ordered_at' => 'datetime'];
}

$order->ordered_at->toDateString();
```

How a date is *displayed* is the client's job, with the active locale
(`.claude/rules/i18n.md`); pass ISO strings in props, not a formatted `m-d`.

## Use `whereBelongsTo()` for relationship queries

```php
Post::whereBelongsTo($user)->get();
Post::whereBelongsTo($user, 'author')->get();
```

rather than `Post::where('user_id', $user->id)`.

## Keep application queries model-aware

Prefer Eloquent models and relationships for model-backed queries — they keep casts, scopes and
the model's table configuration. `DB::table()`, joins and raw SQL legitimately need table names;
use them when their lower-level behaviour is intentional, and keep those references covered by a
test.

```php
User::where('active', true)->get();
Order::where('status', OrderStatus::Pending)->get();
```

Use `(new User)->getTable()` where a builder operation should follow the model's table name.

In migrations, use explicit table names, never models: a migration is a historical snapshot, and a
model and its scopes change after the migration is deployed.
