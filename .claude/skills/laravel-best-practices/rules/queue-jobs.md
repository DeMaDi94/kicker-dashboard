# Queues and jobs

Whether work is queued at all, on which queue, and its tries, backoff, timeout and uniqueness
window are decisions — cite the requirement or decision id beside the value. Tests run with
`QUEUE_CONNECTION=sync` (`phpunit.xml`), so a queued job runs inline there; assert dispatch with
`Queue::fake()` when the dispatch itself is the behaviour.

A job orchestrates like a service: it calls the domain rules, it does not contain them.

## Keep reservation time longer than execution time

For drivers that use `retry_after`, set it above the longest worker or job timeout by a safety
margin. When a reservation expires, another worker can reserve the same job while the first is
still running. Keep the worker's `--timeout` a few seconds below `retry_after`.

```php
// Job
public int $timeout = 120;

// config/queue.php, on the connection
'retry_after' => 150,
```

SQS uses its visibility timeout instead. Because a worker can also stop after side effects but
before acknowledging, make important jobs idempotent even with correct timeouts.

## Back off transient failures

Use progressively longer delays when a dependency needs time to recover. Do not retry a permanent
validation or business-rule failure.

```php
final class SyncWithStripe implements ShouldQueue
{
    public int $tries = 4;

    /** @var list<int> */
    public array $backoff = [1, 5, 10];
}
```

Rate-limiting and exception-throttling middleware release jobs back to the queue, and released
attempts may still count toward the limit — set `$tries` or `retryUntil()` for the intended window.

## Use unique jobs for dispatch deduplication

Implement `ShouldBeUnique` when only one queued instance of a logical job may exist. Uniqueness
uses a cache lock; it is not a substitute for idempotent processing or a database constraint.

```php
final class GenerateInvoice implements ShouldQueue, ShouldBeUnique
{
    public int $uniqueFor = 3600;

    public function uniqueId(): string
    {
        return (string) $this->order->id;
    }
}
```

Every dispatching process must share a cache store that supports locks. Uniqueness does not apply
within batches. `ShouldBeUniqueUntilProcessing` releases the lock just before processing starts,
so another instance can be dispatched while the first runs.

## Handle terminal failure when needed

Implement `failed()` when the application must update state, alert someone or record context after
all attempts are exhausted. Logging every failure in every job duplicates the queue's own failure
reporting. Laravel calls `failed()` on a *new* job instance, so state set during `handle()` is not
there.

```php
public function failed(?Throwable $exception): void
{
    $this->podcast->update(['status' => PodcastStatus::Failed]);

    Log::error('Podcast processing failed', [
        'podcast_id' => $this->podcast->id,
        'exception' => $exception,
    ]);
}
```

## Rate limit external calls

Use queue middleware such as `RateLimited` when jobs share a third-party quota. Define the named
limiter and choose release delays and attempt limits together.

```php
/**
 * @return list<object>
 */
public function middleware(): array
{
    return [new RateLimited('external-api')];
}
```

## Batch jobs for group coordination

`Bus::batch()` monitors a group and runs callbacks on completion or failure. A batch is not a
transaction: completed jobs are not rolled back when another fails. By default one failure cancels
the batch; `allowFailures()` only when partial failure is acceptable (and that acceptance is a
decision).

```php
Bus::batch([
    new ImportCsvChunk($chunk1),
    new ImportCsvChunk($chunk2),
])
    ->then(fn (Batch $batch) => Notification::send($user, new ImportComplete))
    ->catch(fn (Batch $batch, Throwable $exception) => Log::error('Import batch failed', [
        'exception' => $exception,
    ]))
    ->dispatch();
```

## Configure time-based retry limits deliberately

`retryUntil()` is the time-based alternative to an attempt count; Laravel retries until the
deadline, subject to other limits such as max exceptions. It takes precedence over `$tries`.

```php
public function retryUntil(): DateTimeInterface
{
    return now()->addHours(4);
}
```

## Horizon

Horizon adds monitoring, balancing and metrics for Redis queues only. It is not installed; adding
it needs approval (`docs/STACK.md`).
