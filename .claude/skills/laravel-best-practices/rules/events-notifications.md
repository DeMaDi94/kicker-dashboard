# Events and notifications

A notification's recipients, channels, and whether it is sent at all come from a requirement (B14's
invitation is one). Whether it is **queued** is a decision too: queue it when a decision says so,
not by reflex. The invitation in `app/Http/Users/StoreUser/UserInvitation.php` is sent synchronously
today.

## Rely on event discovery

Laravel discovers listeners by the event type-hinted on `handle()`/`__invoke()`. Register manually
only when discovery is off, the listener lives elsewhere, or explicit registration is clearer.
`php artisan event:list` shows what is registered. Cache discovery in production with
`php artisan optimize` (or `event:cache`) and rebuild when listeners change.

## Dispatch after commit

An event dispatched inside a transaction can reach its listeners before the transaction commits —
or after it rolls back. `ShouldDispatchAfterCommit` holds dispatch until every open transaction
commits and discards the event on rollback. It affects synchronous and queued listeners alike.

```php
final class OrderShipped implements ShouldDispatchAfterCommit {}
```

The same holds for a queued notification: call `afterCommit()`, or enable the connection's
`after_commit` option, whenever delivery depends on committed data. It has no effect on a
synchronous notification — for those, send after the transaction returns, as `StoreUserService`
does.

```php
$user->notify((new InvoicePaid($invoice))->afterCommit());
```

## Queueing a notification

When a decision says a notification is queued, implement `ShouldQueue`:

```php
final class InvoicePaid extends Notification implements ShouldQueue
{
    use Queueable;
}
```

Keep it synchronous when the caller needs immediate failure feedback, or when no worker runs.
`viaQueues()` routes channels to separate queues when their latency needs differ (again, a
decision).

## On-demand notifications for non-user recipients

```php
Notification::route('mail', $address)->notify(new SystemAlert());
```

Do not create a dummy model to hold an address. The address itself comes from data or config, never
from a literal in code.

## Send in the recipient's locale

Every line of a notification is `__('…')`, one key per sentence with placeholders
(`.claude/rules/i18n.md`). Implement `HasLocalePreference::preferredLocale()` on the notifiable
when the recipient's locale is known, so mail and queued delivery use it; `->locale()` overrides it
for one send. Without a preference the notification renders in the locale active when it is sent —
for an admin creating a user, that is the admin's, which may be a question for the spec.

## Keep rules out of listeners

A listener orchestrates like a service. If it decides something (who is notified, whether a
threshold is crossed), that decision is a domain rule in `app/Domain/{Area}/`.
