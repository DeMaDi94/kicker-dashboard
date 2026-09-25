# Deployment plan

Status: **proposal, not decided.** The production database is still open (B3); nothing below is
in force until it is recorded as a `D*` decision in `DECISIONS.md`.

Researched 2026-09-25. Prices change — check the providers' pricing pages before committing.

## What the app needs in production

- **PHP only at runtime.** One Laravel monolith (B1); Node is needed only to build the assets
  (`npm run build`).
- **No worker and no cron.** Nothing in `app/` is queued and `routes/console.php` schedules
  nothing, so `QUEUE_CONNECTION=sync` is enough today. Revisit when a queued job or a scheduled
  command is added.
- **Outgoing mail.** Password reset and the admin invitation (B14) send mail, so an SMTP service
  is required.
- **HTTPS.** Passkeys (B2) need a secure context.
- **A production database** — open, see below.

## Options, cheapest first

| # | Option | ~ per month | For | Against |
| --- | --- | --- | --- | --- |
| 1 | Oracle Cloud Always Free ARM VM | €0 | Free, generous | Self-managed; idle reclaim and account-closure risk |
| 2 | Hetzner Cloud CX22 (or netcup) + Caddy + PHP-FPM + SQLite | €4–5 | Cheap, reliable, EU/GDPR, Caddy does TLS | Self-managed server; deploy via GitHub Actions + SSH |
| 3 | Option 2 + Laravel Forge / Ploi | €4–5 + €8–12 | Push-to-deploy, SSL, backups without ops work | The panel costs more than the server |
| 4 | Fly.io / Railway container with a volume | $3–7 | Push-to-deploy, no server | Needs a Dockerfile; SQLite only on a persistent volume |
| 5 | **Laravel Cloud, Starter plan** | **$5** | Built for Laravel, scales to zero, no ops | No SQLite — needs Postgres or MySQL |
| 6 | Shared PHP hosting (all-inkl, netcup) | €3–5 | Cheap, zero ops | Needs PHP 8.5, SSH and docroot at `public/`; build assets in CI |

Free tiers without a persistent disk (e.g. Render free) lose a SQLite database on every deploy.

## Laravel Cloud cost estimate

- Starter: $5/month base, **includes $5 usage credit**; first month free.
- Compute bills only while awake. Flex 512 MiB ≈ $0.009 per awake hour, capped at $6/month.
- Laravel's own "pub night quiz tracker" example (30 users, ~4 h awake/month, Flex 512 MiB,
  Serverless Postgres 0.5 GB) is ~$0.39/month — close to this app's profile.
- Expected total: **$5/month**, usage covered by the credit.
- Worst case: something keeps the app awake 24/7 (e.g. a wall screen polling via `useLivePoll`)
  → roughly $6–10/month. Set a spending limit; compute pauses at the cap.

## Open before go-live

1. **Production database (B3).** Laravel Cloud → Postgres; VPS or shared hosting → SQLite is
   enough. Record the choice as a `D*` decision.
2. If Postgres: run the migrations and the test suite against Postgres at least once — tests run
   on SQLite, and e.g. `LIKE` in the user search (B16) is case-sensitive on Postgres.
3. SMTP provider for the Fortify mails (Brevo, Mailjet, Resend free tiers, or the domain's mailbox).
4. Backups: nightly copy of `database/database.sqlite` (or Litestream) for SQLite; managed for
   Postgres on Laravel Cloud.

## Go-live steps (any option)

1. Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://…`, a fresh `APP_KEY`, the
   database and the mail settings.
2. Build: `composer install --no-dev --optimize-autoloader`, `npm ci && npm run build`.
3. Deploy: `php artisan migrate --force`, `php artisan optimize`.
4. Create the first admin: `php artisan users:create-admin` (B14).
