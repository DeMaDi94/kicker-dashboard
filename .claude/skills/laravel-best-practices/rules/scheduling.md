# Task scheduling

Schedules are defined in `routes/console.php` (Laravel 13 slim skeleton — no `Console\Kernel`).
A task's frequency, time of day, timezone and environments are requirements or decisions — cite
the id beside the schedule line. The frequencies below show the API.

## Prevent unwanted overlap

`withoutOverlapping()` stops a second run starting while the previous one holds the lock — for
variable-duration tasks that are not safe to run concurrently.

```php
Schedule::command('reports:generate')
    ->everyFifteenMinutes()
    ->withoutOverlapping(30);
```

The argument is the lock expiry in minutes, not a task timeout. The default is 24 hours; stale locks
are cleared with `php artisan schedule:clear-cache`; too short an expiry permits overlap while the
first run is still going. The task should still tolerate retries and partial execution.

## Run a task on one server

`onOneServer()` lets only one scheduler node run an eligible task. All nodes must share a default
cache store that supports atomic locks (`database`, `memcached`, `dynamodb`, `redis`).

```php
Schedule::command('billing:charge')->daily()->onOneServer();
```

Name scheduled closures before `onOneServer()` so each has a distinct lock identity.

## Run long commands in the background

Tasks due at the same time run sequentially. `runInBackground()` stops a long, independent command
delaying the others. It works only for `command()` and `exec()`, not closures; give background
processes logging and failure monitoring.

```php
Schedule::command('analytics:process')->hourly()->runInBackground();
```

## Restrict tasks by environment

`environments()` is an operational safeguard, not an authorization control.

```php
Schedule::command('billing:charge')->monthly()->environments(['production']);
```

## Group shared configuration

Use a group only when several tasks genuinely share frequency or constraints.

```php
Schedule::daily()
    ->onOneServer()
    ->group(function (): void {
        Schedule::command('emails:send --force');
        Schedule::command('emails:prune');
    });
```

## Bound work inside the task

The scheduler does not terminate a task at a deadline. Bound the work in the command or job
itself — finite chunks, a deadline check, or queued jobs with suitable timeouts.

## Keep the command thin

A scheduled command is an edge like a controller: it calls a service (or the area's Port), which
calls the domain rules. `php artisan schedule:list` shows what is registered;
`php artisan schedule:test` runs one task now.
