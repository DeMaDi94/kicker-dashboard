# Finding test framework features

Pest adds features faster than any list. Find the existing feature before writing it by hand, and
confirm it in the installed source — `vendor/pestphp/pest/src` (expectations in
`src/Mixins/Expectation.php`, CLI plugins in `src/Plugins/`) and
`vendor/pestphp/pest-plugin-laravel/src`. `https://pestphp.com/llms.txt` lists features by release.
If the installed version does not have it, say so; do not write an API you have not confirmed.

| Work you need | Look for |
| --- | --- |
| One test over many inputs | datasets, bound datasets, `goldenVectors()` (`tests/Pest.php`) |
| Assert over many values or a collection | higher-order expectations, `->each` |
| Remove the same setup from every test in a file | `beforeEach()` (configuration only), hooks |
| Apply a convention to the whole codebase | architecture testing (`arch()`, `tests/Architecture/`) |
| Check the suite would catch a defect | mutation testing |
| Find untyped code | type coverage |
| Assert a known format | `toBeEmail()`, `toBeUrl()`, `toBeUuid()`, `toBeUlid()`, `toBeIpAddress()`, `toBeJson()`, `toBeSlug()` |
| A slow suite | `--parallel`, `--profile` |
| Split the suite across CI jobs | `--shard`, `--update-shards` |
| Run only what a change affects | Test Impact Analysis, `--tia` |
| Focus while debugging | `--filter`, `--bail`, `--dirty`, `->only()` (never commit it) |

## Built-in Laravel assertions

Laravel has an assertion for each part of the framework — search
`vendor/laravel/framework/src/Illuminate/Testing/TestResponse.php`,
`.../Foundation/Testing/Concerns/InteractsWithDatabase.php` and the fakes in
`.../Support/Testing/Fakes/` before building a check by hand. Examples: `assertDatabaseHas()`,
`assertModelExists()`, `assertSoftDeleted()`, `assertRedirectToRoute()`, `assertInvalid()`,
`assertInertia()`, `Queue::assertPushed()`, `Notification::assertSentTo()`.

A hand-built check fails with `false is not true`, which names nothing. A framework assertion names
the table, value or response that was wrong.

```php
// Fails with "false is not true"
expect(User::where('email', 'nora@example.com')->exists())->toBeTrue();

// Fails naming the table and the attributes it did not find
$this->assertDatabaseHas('users', ['email' => 'nora@example.com']);
```
