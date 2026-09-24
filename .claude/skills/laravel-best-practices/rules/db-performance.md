# Database performance

## Eager load relationships before iterating

When a relationship will be accessed for many models, eager load it with `with()` to avoid one
initial query plus one relationship query per model (N+1). Lazy loading is reasonable when the
relationship may not be needed or only one model is involved.

Lazy-loaded:

```php
$posts = Post::all();
foreach ($posts as $post) {
    $names[] = $post->author->name;
}
```

Eager-loaded:

```php
$posts = Post::with('author')->get();
foreach ($posts as $post) {
    $names[] = $post->author->name;
}
```

Constrain eager loads when large columns are unnecessary. Include the related model's primary key
and every column Eloquent needs to match the relationship — here `users.id` and `posts.user_id`
match posts to users, and `posts.id` keeps each related model's key:

```php
$users = User::with(['posts' => function (Builder $query): void {
    $query->select('id', 'user_id', 'title')->where('published', true);
}])->get();
```

## Prevent lazy loading in development

`Model::preventLazyLoading(! app()->isProduction())` in `AppServiceProvider::boot()` turns an N+1
into a `LazyLoadingViolationException` during development and tests. This repo does not enable it
yet; turning it on is a project-wide change — propose it, do not slip it into a feature.

## Select only needed columns

Select only the columns the operation needs when omitting large text, binary or JSON columns
gives a meaningful benefit.

```php
$posts = Post::select('id', 'title', 'user_id', 'created_at')
    ->with(['author:id,name'])
    ->get();
```

When limiting columns, keep every key Eloquent needs for matching: a `belongsTo` needs its foreign
key on the parent query and the owner's key on the related query; a `hasMany` needs the parent's
local key and the related model's foreign key.

## Process large data sets incrementally

Use chunking or lazy iteration when loading the whole result set would exceed the practical memory
budget. The chunk size is a tuning value, not a rule.

Loads everything:

```php
foreach (User::all() as $user) {
    $user->notify(new WeeklyDigest);
}
```

Bounded chunks:

```php
User::where('subscribed', true)->chunk(200, function (Collection $users): void {
    foreach ($users as $user) {
        $user->notify(new WeeklyDigest);
    }
});
```

Use `chunkById()` when updates can change which rows match the query — `chunk()` uses offset
pagination, whose positions shift as rows change:

```php
User::where('active', false)->chunkById(200, function (Collection $users): void {
    $users->each->delete();
});
```

For read-only, attribute-only iteration, `cursor()` hydrates models one at a time from one query
(some drivers still buffer raw results). Use `lazy()` when relationships must be eager loaded per
chunk, and `lazyById()`/`chunkById()` when updates can affect membership. See
[`collections.md`](collections.md).

## Add indexes for measured query patterns

Design indexes around frequent, performance-sensitive queries. A column appearing in `WHERE`,
`ORDER BY`, `JOIN` or `GROUP BY` does not by itself justify an index; selectivity, write cost,
existing indexes and the query plan all matter.

Schema optimised for `WHERE status = ? ORDER BY created_at`:

```php
Schema::create('orders', function (Blueprint $table): void {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->string('status');
    $table->timestamps();
    $table->index(['status', 'created_at']);
});
```

Confirm composite index order and effectiveness with production-like data and the query plan
(development runs SQLite — B3 — so check the plan on the production engine too). Check whether the
database already indexes a foreign key before adding another index. The foreign key's delete
behaviour is its own decision — see [`migrations.md`](migrations.md).

## Count relationships without loading them

Use `withCount()` when only counts are needed.

```php
$posts = Post::withCount([
    'comments',
    'comments as approved_comments_count' => function (Builder $query): void {
        $query->where('approved', true);
    },
])->get();

$posts->first()?->approved_comments_count;
```

## Keep queries out of Inertia props

A props mapper (like `app/Http/Users/Shared/UserRow.php`) runs once per row. A relationship it
touches that the service did not eager load is an N+1 hidden in serialization. The service loads
everything the props need; the mapper only reads.

```php
// ListUsersService — loads what UserRow reads
$users = User::query()->with('roles')->paginate();

// UserRow::from() — reads the loaded relation, never queries
'role' => $user->assignedRole()?->value,
```
