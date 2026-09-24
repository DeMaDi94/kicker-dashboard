---
name: laravel-best-practices
description: Laravel backend patterns for this repo — Eloquent queries and N+1, subqueries and indexes, migrations and foreign keys, jobs and retries, caching and locks, outbound HTTP clients, exceptions, events, notifications and mail, scheduling, collections, validation in FormRequests, routing and model binding, security and authorization. Load when writing, reviewing or refactoring PHP in app/, routes/, database/ or config/ that touches any of these; not for the domain rules themselves (pure PHP in app/Domain) and not for tests (use testing-best-practices).
license: MIT
metadata:
  source: "laravel/boost@v2.10.0 .ai/laravel/skill/laravel-best-practices"
  adapted: "Fitted to the harness contract: one-folder-per-action Http/{Area}/{Action} with an invokable controller, a FormRequest and a Service; Ports across areas; framework-free app/Domain; every suggested value needs a requirement or decision id; spatie/laravel-permission checks by permission (B13); __() for user text; Boost MCP tools replaced by artisan/vendor lookups; Blade rules dropped."
---

# Laravel best practices

`.claude/rules/architecture.md`, `.claude/rules/requirements.md` and `.claude/rules/php.md`
override every rule here. Where a rule here suggests a value — a TTL, a retry count, a timeout, a
queue, a chunk size, a sort order, a delete behaviour — that value still needs a requirement or
decision id. The numbers in the examples show the API, not a default to copy.

## How to apply

1. Map every concern the change touches to the index below and read those rule files — only those.
2. Read a sibling action under `app/Http/{Area}/` (e.g. `app/Http/Users/StoreUser/`) and match it.
3. Verify a version-sensitive API against the installed package source under `vendor/<pkg>`
   (`vendor/laravel/framework`, `vendor/spatie/laravel-permission`, …), not from memory.
4. Make the smallest coherent change (`.claude/rules/minimalism.md`).
5. Run the narrowest test first, then `composer test`.
6. Re-read the diff against every rule file you selected.

## Rule index

| Concern | Read |
| --- | --- |
| Query count, eager loading, column selection, large datasets, indexes | [`rules/db-performance.md`](rules/db-performance.md) |
| Subqueries, conditional aggregates, has-many ordering, composite indexes | [`rules/advanced-queries.md`](rules/advanced-queries.md) |
| Relationships, scopes, casts, model-aware queries | [`rules/eloquent.md`](rules/eloquent.md) |
| Mass assignment, authorization by permission, SQL bindings, uploads, secrets | [`rules/security.md`](rules/security.md) |
| `{Action}Request` rules, `validated()`, conditional and cross-field rules | [`rules/validation.md`](rules/validation.md) |
| Model binding, scoped bindings, named routes, Wayfinder | [`rules/routing.md`](rules/routing.md) |
| Schema changes, foreign keys, staged backfills, rollbacks | [`rules/migrations.md`](rules/migrations.md) |
| Jobs: timeouts, retries, uniqueness, batches, failure | [`rules/queue-jobs.md`](rules/queue-jobs.md) |
| Cache-aside, stale-while-revalidate, memoization, tags, locks | [`rules/caching.md`](rules/caching.md) |
| Outbound requests: timeouts, safe retries, errors, pools, fakes | [`rules/http-client.md`](rules/http-client.md) |
| Exception reporting and rendering, context, throttling | [`rules/error-handling.md`](rules/error-handling.md) |
| Events, listeners, notifications, after-commit dispatch, locale | [`rules/events-notifications.md`](rules/events-notifications.md) |
| Mailables, queued vs sync delivery, mail assertions | [`rules/mail.md`](rules/mail.md) |
| Scheduled tasks, overlap, one-server, bounded work | [`rules/scheduling.md`](rules/scheduling.md) |
| Higher-order messages, `lazy()`/`cursor()`, `toQuery()` | [`rules/collections.md`](rules/collections.md) |
| `env()` vs `config()`, secrets, enums, what is config and what is data | [`rules/config.md`](rules/config.md) |
| Naming, idiomatic helpers, `Str`/`Arr`/`Number`, comments | [`rules/style.md`](rules/style.md) |
| Injection, deterministic order, locks, `mb_*`, `defer()`, `Context`, `Concurrency` | [`rules/architecture.md`](rules/architecture.md) |
| Tests | the `testing-best-practices` skill |

## Decision rules

- Prefer the framework and what the repo already has over a new helper or dependency.
- No speculative abstraction: an extraction needs a second caller, a domain boundary or a test it
  makes possible.
- A service that starts deciding (an amount, a date, a status) has a domain rule in it — move the
  rule to `app/Domain/{Area}/` and call it.
- Keep N+1 queries out of services, Inertia props, jobs and serialization alike.
