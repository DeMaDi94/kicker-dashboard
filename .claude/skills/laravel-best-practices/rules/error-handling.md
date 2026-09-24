# Error handling

The exception policy lives in `bootstrap/app.php` → `withExceptions()` (Laravel 13 slim skeleton —
there is no `app/Exceptions/Handler.php`). Every message a user reads is `__('…')`.

## Choose where to report and render

Exception-specific methods keep behaviour beside the exception; centralised callbacks keep the
policy together. Pick one per exception and do not mix both for the same class.

```php
final class InvalidOrderException extends Exception
{
    public function report(): void
    {
        // Send to a custom reporter.
    }

    public function render(Request $request): RedirectResponse
    {
        Inertia::flash('toast', ['type' => 'error', 'message' => __('This order can no longer be changed.')]);

        return back();
    }
}
```

```php
->withExceptions(function (Exceptions $exceptions): void {
    $exceptions->report(function (InvalidOrderException $e): void {
        // Send to a custom reporter.
    });
    $exceptions->render(function (InvalidOrderException $e, Request $request): RedirectResponse {
        Inertia::flash('toast', ['type' => 'error', 'message' => __('This order can no longer be changed.')]);

        return back();
    });
})
```

In an Inertia app a rendered refusal is usually a redirect back with a translated toast (the
pattern the Users area uses) or a validation error on the field — not a Blade error view. An
expected domain refusal (a guard rule saying no, like B15's `AccountGuard`) is not an exception to
report at all.

An exception's `report()` suppresses default reporting unless it returns `false`. A report callback
allows default reporting unless it returns `false` or is chained with `stop()`. Returning `false`
from `render()` or a render callback defers to Laravel's default rendering.

## Mark exceptions the handler should not report

`ShouldntReport` keeps the policy visible on the class. It does not stop code from logging the
exception explicitly.

```php
final class PodcastProcessingException extends Exception implements ShouldntReport {}
```

## Throttle high-volume reports

A failing integration can flood logs. `throttle()` with a `Lottery` or `Limit` samples or
rate-limits matching reports; choose keys so separate integrations get independent limits.

## Prevent duplicate reports of one instance

`dontReportDuplicates()` deduplicates by object identity when the same exception passes through
several `report($e)` calls — not by class or message.

## JSON rendering

`bootstrap/app.php` already renders JSON for `api/*` and for requests that expect JSON. Change it
only with a decision; there are no API routes yet (B1 — one Inertia monolith).

## Add context to exception classes

`context()` data is merged into the log context when the handler reports the exception.

```php
/**
 * @return array<string, int>
 */
public function context(): array
{
    return ['order_id' => $this->orderId];
}
```

Never put a secret or a password-reset token in context.
