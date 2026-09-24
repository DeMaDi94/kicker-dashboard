# Architecture helpers

The application's shape — `app/Domain/{Area}` for rules, `app/Http/{Area}/{Action}/` with an
invokable controller, a `FormRequest` and a `final` single-`__invoke` Service, `Ports/` across areas
— is fixed by `.claude/rules/architecture.md` (B4). Do not introduce Action classes, a global
`Services/` folder, repositories, or an interface with one implementation. This file covers the
framework tools that fit inside that shape.

## Inject dependencies

Constructor injection for what a service needs throughout; method injection in the controller's
`__invoke` for the service itself. Avoid `app()`/`resolve()` in application code when injection
makes the dependency explicit (a test may resolve a service from the container to call it).

```php
final class StoreUserController
{
    public function __invoke(StoreUserRequest $request, StoreUserService $store): RedirectResponse
    {
        $store($request->toInput());

        return to_route('users.index');
    }
}
```

A boundary to an external system is a concrete client class in the owning area, faked at the HTTP
layer in tests (`Http::fake()`) — not an interface bound in a provider "for testability".

## Every list query has an explicit order — from the spec

Without `ORDER BY`, row order is undefined, so every list and every paginated query orders
explicitly and adds a unique tie-breaker for stable pagination. **Which** order is a requirement or
a decision; B16 fixes the user list at name A–Z. If the spec names none, ask — "newest first" is
an invented default.

```php
// B16 — opens sorted by name A–Z, 20 per page; id breaks ties for stable pages
$users = User::query()
    ->orderBy('name')
    ->orderBy('id')
    ->paginate(20);
```

## Atomic locks for race conditions

Use a lock when concurrent execution must be serialised. `Cache::lock()` gives an atomic lock when
the store supports it; `lockForUpdate()` locks rows and must run inside a transaction. They solve
different problems. Both belong in the service, never the controller or the domain.

```php
Cache::lock('order-processing-'.$order->id, 10)->block(5, function () use ($order): void {
    $order->process();
});

DB::transaction(function () use ($id): void {
    $product = Product::where('id', $id)->lockForUpdate()->first();

    // read and update while the row lock is held
});
```

A gapless sequence (an invoice number) is the classic case: the rule that computes the next number
lives in `app/Domain`, the lock and the read of the current maximum live in the service.

## Use `mb_*` string functions

Where no `Str` helper exists, prefer multibyte-aware functions for UTF-8 text — this matters for
German umlauts (B6). `app/Domain` may use `mb_*` freely; `Str` is fine there too
(`illuminate/support` needs no booted app), facades are not.

```php
strlen('José');          // 5 bytes, not 4 characters
strtolower('MÜNCHEN');   // does not lowercase Ü

mb_strlen('José');          // 4
mb_strtolower('MÜNCHEN');   // 'münchen'
Str::lower('MÜNCHEN');      // 'münchen'
```

## `defer()` for post-response work

For lightweight work that needs no retries or crash durability, `defer()` runs a callback after the
response is sent, in the same PHP process. Use a queued job when the work needs retries, queue
controls or durability. Which one is a decision when a user would notice the difference.

```php
defer(fn () => PageView::create(['page_id' => $page->id, 'user_id' => $userId]));
```

## `Context` for request-scoped data

`Context` carries data across the current execution without threading it through every call.
Visible context is added to logs; visible and hidden context are captured and restored for queued
jobs. Use `Context::addHidden()` for data that should reach jobs but not logs. Never put a secret
in context unless that propagation is intended. Set it in middleware or a service — `app/Domain`
never reads it.

```php
Context::add('request_id', $requestId);

Context::get('request_id');
```

## `Concurrency::run()` for parallel work

Runs independent operations through the configured concurrency driver.

```php
[$userCount, $pendingOrders] = Concurrency::run([
    fn () => User::count(),
    fn () => Order::where('status', OrderStatus::Pending)->count(),
]);
```

With the process driver each closure boots the app in its own PHP process — worth it only when the
operations are slow enough to offset that. The `sync` driver runs them sequentially (tests).

## Follow framework conventions

Conventional table names, primary keys and pivot names unless an existing schema requires otherwise;
every override is configuration a reader has to know about.
