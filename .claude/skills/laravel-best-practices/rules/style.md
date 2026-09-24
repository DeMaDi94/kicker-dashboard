# Conventions and style

`.claude/rules/php.md` is the style contract (strict types, promotion, explicit types, no FQCN,
braces everywhere, Pint applied automatically). This file adds Laravel's naming and helper idioms.

## Naming

Domain terms come from `docs/GLOSSARY.md`; look a term up rather than naming it yourself.

| Element | Convention | Example |
| --- | --- | --- |
| Action folder | `{Verb}{Thing}` under `app/Http/{Area}/` | `app/Http/Users/StoreUser/` |
| Controller / Request / Service | `{Action}Controller`, `{Action}Request`, `{Action}Service` | `StoreUserController` |
| Port | `{Thing}Port` in `app/Http/{Area}/Ports/` | `InvoiceTotalsPort` |
| Model | Singular StudlyCase | `User` |
| Table | Plural snake_case | `article_comments` |
| Pivot table | Singular model names, alphabetical, snake_case | `article_user` |
| Column | snake_case | `meta_title` |
| Foreign key | Singular model name + `_id` | `article_id` |
| Resource URI | Plural | `settings/users/1` |
| Route name | Dotted segments, kebab-case within a segment | `users.password-reset-link` |
| Method | camelCase | `assignedRole` |
| Variable, property, parameter | camelCase | `$activeUsers` |
| Props key (Inertia) | camelCase | `emailVerifiedAt` |
| Collection | Descriptive, plural | `$activeUsers` |
| Object | Descriptive, singular | `$activeUser` |
| Config file | snake_case | `google_calendar.php` |
| Enum | Singular StudlyCase in `app/Domain/{Area}/`, TitleCase cases | `Role::Admin` |

## Prefer clear, idiomatic syntax

Use Laravel helpers and query methods when they say the intent more directly — not when the
shorter form is ambiguous or loses type information.

| More verbose | Idiomatic |
| --- | --- |
| `Session::get('cart')` | `session('cart')` |
| `return Redirect::back()` | `return back()` |
| `return redirect()->route('users.index')` | `return to_route('users.index')` |
| `Carbon::now()` | `now()` |
| `->where('column', '=', 1)` | `->where('column', 1)` |
| `->orderBy('created_at', 'desc')` | `->latest()` |
| `->orderBy('created_at', 'asc')` | `->oldest()` |
| `->first()?->name` | `->value('name')` when only that value is needed |

`latest()` is a shorter spelling, not a licence to choose an order: which way a list is sorted is a
requirement or decision.

Use typed request accessors (`$request->string()`, `->integer()`, `->boolean()`) in the
`{Action}Request` when their coercion matches — and nowhere else, since nothing else sees the
request.

## Use `Str`, `Arr`, `Number` and `Uri` when they clarify intent

They are clearer or safer than the PHP built-in in many cases — not an unconditional replacement.
`Str` is multibyte-safe where `strlen()`/`strtolower()` are not (see
[`architecture.md`](architecture.md)).

```php
$slug = Str::slug($title);
$short = Str::limit($text, 100);
$result = Str::of($input)->trim()->replace('_', '-')->lower();

$name = Arr::get($array, 'user.name');
$public = Arr::only($attributes, ['name', 'email']);

$uri = Uri::of('https://example.com/search')->withQuery(['q' => 'laravel', 'page' => 1]);
```

`Number` is for display, never for values that are stored or calculated — and always in the active
locale, never a hard-coded one (B6):

```php
Number::format($count, locale: app()->getLocale());
Number::currency($amount, 'EUR', locale: app()->getLocale());
Number::fileSize($bytes);
```

Most display formatting happens on the client with `lib/number.ts` and the active locale; format on
the server only for text the server renders itself (mail, an export).

The `limit` in `Str::limit()`, a currency code, a fallback in `Arr::get()` — each is a value, and
needs an id if a user sees its effect.

## Comments explain why — and cite the requirement

Prefer clear names and small units over comments that restate the code. A comment earns its place
for a non-obvious *why*: a constraint, a workaround, external behaviour — and above all **which
requirement or decision a line exists for**. That citation is required, not optional
(`.claude/rules/requirements.md`).

```php
// B15 — a deleted user's address stays taken: restore that user instead.
if (User::onlyTrashed()->where('email', $value)->exists()) {
```

Unhelpful:

```php
// Check whether the query has joins.
if (count((array) $builder->getQuery()->joins) > 0) {
```

Better — name it:

```php
if ($this->hasJoins()) {
```

Prefer a PHPDoc block on the class or method over an inline comment. Keep comments true when the
behaviour changes.

## Presentation belongs to React

Server code hands Inertia an explicit props shape; the page composes it. No inline scripts, no
serialised models in attributes, no styling decisions on the server.
