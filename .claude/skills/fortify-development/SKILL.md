---
name: fortify-development
description: Laravel Fortify as this repo runs it (decision B14) — login, logout, password reset and the invitation that reuses it, e-mail verification, two-factor (TOTP, QR code, recovery codes), passkeys, password confirmation, login throttling, and the Inertia views Fortify renders. Load when touching config/fortify.php, app/Providers/FortifyServiceProvider.php, app/Actions/Fortify/, resources/js/pages/auth/, the security settings screen, the invitation mail, or any auth route; or when someone asks for registration, sign-up or a new auth feature. Not for roles and permissions (laravel-permission-development), Passport, Sanctum API tokens or Socialite.
license: MIT
metadata:
  source: "laravel/fortify@v1.40.0 resources/boost/skills/fortify-development/SKILL.md"
  adapted: "Scoped to B14 (no self-registration, admin-created accounts, invitation via a reset token, e-mail verification, 2FA, passkeys); SPA/Sanctum and registration/CreateNewUser guidance dropped; features enabled only by decision; route discovery via artisan route:list; endpoint table replaced by the install's own route list."
---

# Laravel Fortify development

Fortify is the headless auth backend: it registers the auth routes and controllers, and this app
supplies the Inertia pages it renders. **How it is configured is decided, not open**: read
decision B14 (and B15 for deleted users) in `docs/DECISIONS.md` before changing anything here.

## What B14 fixes

- **No self-registration.** `Features::registration()` is off and stays off;
  `tests/Feature/Auth/RegistrationRemovedTest.php` holds it. There is no `CreateNewUser` action
  and no register page. A product that wants public sign-up records a `D*` decision first — do not
  re-enable it because a task "needs a sign-up form".
- **Accounts are created by an admin** (name, address, role) through
  `app/Http/Users/StoreUser/`. The new user gets `UserInvitation` — a mail carrying a
  **password-reset token** that opens Fortify's own reset page. The link expires as reset links do
  (`config/auth.php` → `passwords.users.expire`).
- **Setting a password through an emailed link verifies the address**
  (`app/Actions/Fortify/ResetUserPassword.php`), so an invited user needs no second mail.
- **The first admin** of an installation comes from `php artisan users:create-admin`.
- **Addresses are stored lowercased** (`lowercase_usernames` is on): anything that writes an
  e-mail lowercases it first, or the account cannot sign in.
- **Deleted users** (B15, soft deletes) cannot sign in with a password or a passkey
  (`App\Models\Passkey` is registered in `FortifyServiceProvider::configurePasskeys()`).

## Enabled features

`config/fortify.php` → `features` is exactly what B14 lists:

| Feature | Note |
| --- | --- |
| `Features::resetPasswords()` | Also the invitation path. |
| `Features::emailVerification()` | `User` implements `MustVerifyEmail`; protect routes with `verified`. |
| `Features::twoFactorAuthentication(['confirm' => true, 'confirmPassword' => true])` | `User` uses `TwoFactorAuthenticatable`; columns from `*_add_two_factor_columns_to_users_table.php`. |
| `Features::passkeys(['confirmPassword' => true])` | `User` implements `PasskeyUser` + `PasskeyAuthenticatable`; frontend via `@laravel/passkeys`. |

Not enabled, and not to be enabled without a decision: `registration()`,
`updateProfileInformation()`, `updatePasswords()`. Profile and password changes are the starter
kit's settings controllers (`app/Http/Controllers/Settings/`), not Fortify actions. Changing a
feature's options (the 2FA `window`, `confirmPassword`) is a decision too.

## Where things are

- **Routes** — list what Fortify and Passkeys actually registered rather than trusting a table:
  `php artisan route:list --only-vendor --path=two-factor` (or `--path=passkeys`,
  `--path=user`, `--path=reset-password`, …). From React, use the Wayfinder helpers
  (`@/routes/two-factor`, `@/routes/password`, `@/routes/login`,
  `@/actions/Laravel/Passkeys/Http/Controllers/…`), never a literal path.
- **Views** — every view callback is in `FortifyServiceProvider::configureViews()`, each an
  `Inertia::render('auth/…', [...])` of a page under `resources/js/pages/auth/`. A new Fortify
  screen follows the same shape; its text goes through `t('…')` and every locale file.
- **Actions** — `app/Actions/Fortify/ResetUserPassword.php` is the only Fortify action. It is
  scaffolding outside the `app/Http/{Area}/{Action}/` pattern: change it in place when B14
  requires it, do not grow new files there. Business rules it needs belong in `app/Domain/`.
- **Rate limits** — `configureRateLimiting()` defines `login`, `two-factor` and `passkeys`. The
  limits are the starter kit's; a different number needs a decision id.
- **Response contracts** — `Laravel\Fortify\Contracts\LoginResponse`, `LogoutResponse`, … can be
  rebound for a custom redirect. `home` is `/dashboard` (B12). Do not invent a different landing
  route.
- **Custom authentication** — `Fortify::authenticateUsing()` / `authenticateThrough()` replace the
  user lookup or the pipeline. Soft deletes already keep deleted users out; reach for these only
  when a requirement adds a sign-in condition.
- **Source** — for anything this file does not cover, read `vendor/laravel/fortify/src/` (the
  `Features` class, `FortifyServiceProvider`, `routes/routes.php`) and
  `vendor/laravel/passkeys/`.

## Checklists

### Changing the invitation or the reset flow

```text
- [ ] Re-read B14; the invitation is a reset token, not a second token system
- [ ] UserInvitation (app/Http/Users/StoreUser/) and ResetUserPassword stay in step
- [ ] Mail lines through __('…'), every locale in lang/{locale}.json
- [ ] tests/Feature/Auth/PasswordResetTest.php and tests/Feature/Users/StoreUserTest.php pass
```

### Two-factor or passkey UI

```text
- [ ] Routes via Wayfinder (@/routes/two-factor, the Passkeys actions)
- [ ] QR code / secret / recovery codes fetched with useHttp (non-page JSON, B1) — see hooks/use-two-factor-auth.ts
- [ ] Confirm-password gate respected (confirmPassword => true)
- [ ] Text through t('…'); errors surfaced, never swallowed
- [ ] tests/Feature/Auth/TwoFactorChallengeTest.php and tests/Feature/Settings/SecurityTest.php pass
```

### E-mail verification

```text
- [ ] Routes that need a verified address carry the verified middleware
- [ ] A user who changes their own address on the profile is unverified again (Settings/ProfileController already does this; keep it)
- [ ] tests/Feature/Auth/EmailVerificationTest.php passes
```

## Debugging

- What is registered: `php artisan route:list --only-vendor --path=…`.
- What a user looks like: `php artisan tinker --execute 'dump(App\Models\User::firstWhere("email", "test@example.com")?->only(["email_verified_at", "two_factor_confirmed_at"]));'`
- What the server is saying while a login misbehaves: `php artisan pail`.

## Pitfalls

- Re-enabling registration, or adding a sign-up link to the login page — B14 forbids it.
- Storing an address with capitals — Fortify lowercases the login input and will not find it.
- A second invitation mechanism beside the reset token.
- Returning JSON for auth routes: `views` is `true`; this is an Inertia app, not a headless SPA.
- Hardcoding `/login`, `/two-factor-challenge` or `/user/passkeys` in a component.
