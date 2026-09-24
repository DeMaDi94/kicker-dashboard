# Mail

Prefer a notification with a `MailMessage` (as `UserInvitation` does) for a transactional mail to a
user; a Mailable when the mail is not addressed to a notifiable or needs its own template. Either
way, every line is `__('…')`, one key per sentence.

## Queue delivery when a decision says so

`ShouldQueue` on a mailable queues it even when the call site uses `Mail::send()`. Whether a mail
is queued is a decision: keep it synchronous when the caller must know at once whether delivery was
accepted, or when no worker is available.

```php
final class OrderShipped extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
}
```

## Dispatch queued mail after commit

A queued mailable dispatched inside a transaction can be processed before the commit. Call
`afterCommit()`, or enable the connection's `after_commit`, whenever the mail depends on committed
records. On rollback an after-commit mailable is not sent. This has no effect on synchronous
delivery — send that after the transaction returns.

```php
Mail::to($user)->send((new OrderShipped($order))->afterCommit());
```

## Assert the delivery mode

`Mail::assertQueued()` for a mailable that implements `ShouldQueue`, `Mail::assertSent()` for a
synchronous one — asserting the wrong one fails. Notifications use `Notification::fake()` and
`Notification::assertSentTo()`.

```php
Mail::assertQueued(OrderShipped::class);
```

## Markdown mailables

Markdown mailables render HTML and plain text from Laravel's mail components and publishable themes
— a good fit for conventional transactional mail. A custom HTML and text pair suits a specialised
design. Brand colours in a published mail theme follow the tokens in `resources/css/theme.css`
(B8), copied as values, since mail clients cannot read CSS variables.

```bash
php artisan make:mail OrderShipped --markdown=mail.orders.shipped
```

## Separate content and delivery tests

Test the rendered content by building the mailable (or `toMail()`) and asserting on it; test
delivery separately with the fake. A failure then names the broken half. Assert content against
the translation catalogue or the requirement's wording, never text copied from the implementation.

## Previewing

`php artisan pail` tails the log while developing; with the `log` mailer the rendered mail lands
there. Links in mail are absolute and use `APP_URL`.
