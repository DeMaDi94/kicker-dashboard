# Configuration

`config/` holds genuinely fixed constants and environment wiring — endpoints, credentials, the
supported locales. Anything the team can edit from a screen is **data** (a shared-settings model),
never config: an editable default in `config/` silently stops being editable
(`.claude/rules/architecture.md` → Configuration).

## Read environment variables only in config files

After `config:cache`, Laravel no longer loads `.env`; `env()` outside `config/` returns `null`.

```php
// Wrong, in app code
$key = env('API_KEY');

// config/services.php
'example' => ['key' => env('EXAMPLE_API_KEY')],

// app code
$key = config('services.example.key');
```

## Protect production secrets

Never commit plaintext secrets. Laravel can encrypt an environment file; a hosting platform can
inject secrets from its own store.

```bash
php artisan env:encrypt --env=production --readable
php artisan env:decrypt --env=production
```

## Environment checks

```php
if (app()->isProduction()) {
    // …
}

if (app()->environment('local', 'testing')) {
    // …
}
```

Never `env('APP_ENV') === 'production'`.

## Name constrained values with an enum in `app/Domain`

A constrained set of values is a backed enum in `app/Domain/{Area}/`, next to the rules that use
it — not `TYPE_*` class constants and not a global `Enums/` folder. Cases are TitleCase and English
(`docs/GLOSSARY.md`); the stored value is the wire format and is not free to change once data
exists.

```php
// Wrong
return $this->type === self::TYPE_NORMAL;

// Right — app/Domain/Orders/OrderType.php
enum OrderType: string
{
    case Normal = 'normal';
    case Express = 'express';
}

return $this->type === OrderType::Normal;
```

Add the enum to the glossary's enum table in the same change.

## User-facing text is always translated

Every string a user reads goes through `__('…')`, keyed by the English text, with an entry in every
`lang/{locale}.json` (B6, `.claude/rules/i18n.md`). There is no "simple literal is fine" case. An
enum's label is a translation key, not its value.

```php
Inertia::flash('toast', ['type' => 'success', 'message' => __('User created. An invitation is on its way.')]);
```

## Fixed constants stay fixed

A value the spec states as fixed ("at most 3") is a constant in the domain rule, with the id — not a
config key and not a parameter nobody sets (`.claude/rules/minimalism.md`).
