# Security

## Control mass assignment

Define `$fillable` when a model is filled from request-derived arrays. Laravel guards all attributes
by default; `$guarded = []` opts out of that protection.

```php
protected $fillable = [
    'name',
    'email',
    'password',
];
```

Never pass untrusted data to a model with `$guarded = []`. Mass-assignment protection controls
which attributes `create()`, `fill()` and `update()` may set — it neither validates values nor
authorizes the operation. In this repo the service receives a typed input object built from
`validated()` (see `StoreUserRequest::toInput()`), so the attribute list is explicit anyway.

## Authorize by permission, never by role (B13)

Roles and permissions come from `spatie/laravel-permission`. Code checks a **permission** —
`App\Domain\Users\Permission` (`users.view`, `users.create`, …) — never a role name. A project adds
a role without touching a single check.

Route-level, as `routes/settings.php` does it:

```php
Route::patch('settings/users/{user}', UpdateUserController::class)
    ->can(Permission::UpdateUsers->value)
    ->name('users.update');
```

Or in the `{Action}Request` when the check depends on the bound model:

```php
public function authorize(): bool
{
    return $this->user()?->can(Permission::UpdateUsers->value) ?? false;
}
```

Never:

```php
$user->hasRole('admin');          // a role name in a check
$user->assignedRole() === Role::Admin;
```

- A new permission is a requirement or decision; it is created by a migration (B13) so every
  environment has it, and it reaches the client through the shared `auth.permissions` prop.
- A rule that says *who may do what to whom* beyond a plain permission (B15's "cannot delete your
  own account", "the last admin") is a domain rule in `app/Domain/{Area}/` (`AccountGuard`), called
  by the service — not an `if` in a controller.
- Authentication is not authorization, and validation is not authorization. Public actions need no
  redundant check.

## Bind query parameters

Use Eloquent, the query builder or explicit bindings instead of interpolating values into SQL.
Bindings protect values, not identifiers — map a user-chosen column or direction to an allow-list
first (as `ListUsersQuery` does for the user list's sort).

```php
// Wrong
DB::select("SELECT * FROM users WHERE name = '{$name}'");

// Right
User::where('name', $name)->get();
User::whereRaw('LOWER(name) = ?', [Str::lower($name)])->get();
```

## Escape output in its context

React escapes what it renders. `dangerouslySetInnerHTML` needs content sanitised for exactly that
HTML context. In mail, `{{ }}` escapes and `{!! !!}` does not. URL, JavaScript and CSS contexts each
escape differently.

## CSRF

Inertia's client sends the `XSRF-TOKEN` cookie back as the `X-XSRF-TOKEN` header. Do not disable
CSRF verification to fix a token mismatch. A route excluded from it (a validated third-party
webhook) needs its own authenticity check.

## Rate limit sensitive endpoints

Login, two-factor and passkeys have named limiters in `app/Providers/FortifyServiceProvider.php`;
reset links are throttled by the password broker (`config/auth.php`). A new abuse-prone endpoint gets a named limiter; the
limit itself is a decision. Choose the key deliberately — an IP alone groups users behind a shared
network, an account id alone enables targeted lock-out.

```php
RateLimiter::for('export', function (Request $request): Limit {
    return Limit::perMinute(5)->by($request->user()?->id.'|'.$request->ip());
});
```

Rate limiting reduces abuse; it replaces neither authorization nor upstream DoS protection.

## Validate and store uploads safely

Validate content type, dimensions where relevant, and size. `mimes` inspects the content and
guesses a MIME type; `extensions` checks only the filename — never use it alone. The size limit is a
requirement or decision.

```php
'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
```

Let Laravel generate the filename, and store untrusted files outside a publicly executable
location.

```php
$path = $request->file('avatar')->store('avatars');
```

## Keep secrets out of code

Never commit a populated `.env`. Read environment variables only in `config/`, then use `config()`
(see [`config.md`](config.md)).

## Audit dependencies

`composer audit` in CI and before a release. Adding or bumping a dependency needs approval
(`docs/STACK.md`).

## Encrypt sensitive attributes when appropriate

Use the `encrypted` cast for sensitive values that must be recoverable, and `$hidden` to keep them
out of serialization. Hidden attributes stay readable in PHP; encryption does not replace access
control. Encrypted values cannot be queried and need a `TEXT` (or larger) column.

```php
protected $hidden = ['api_key', 'api_secret'];

protected function casts(): array
{
    return [
        'api_key' => 'encrypted',
        'api_secret' => 'encrypted',
    ];
}
```

Props are an explicit shape (`{Screen}Props`), never a raw model — so a hidden attribute cannot leak
through an Inertia prop either.
