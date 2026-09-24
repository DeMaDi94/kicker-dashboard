# Assertions

## Arrange, act, assert

Three parts — setup, one action, assertions — separated by one blank line, no comments needed.
Each test is self-contained and never uses values another test created.

## Choosing the assertion

Identify the subject, then use the assertion made for it; a subject-specific assertion names the
wrong value when it fails.

| Subject | Use |
| --- | --- |
| A return value, an object's state, a transformation — every domain test | an `expect()` chain |
| An HTTP status, a redirect, the session, validation errors, Inertia | a Laravel response assertion |
| The database | a Laravel database assertion |
| A model exists / was soft-deleted | `assertModelExists($model)`, `assertSoftDeleted($model)` — not `assertDatabaseHas('users', ['id' => …])` |

A PHPUnit assertion only when neither Pest nor Laravel has one. Confirm a name in the installed
source before using it (see [`finding-features.md`](finding-features.md)).

Keep one `expect()` chain on one subject; start a new chain when the subject changes or the chain
stops reading well.

## Named response assertions

`assertOk()`, `assertCreated()`, `assertNoContent()`, `assertRedirect()`, `assertForbidden()`,
`assertNotFound()`, `assertUnprocessable()`, `assertInvalid()` — never `assertStatus(403)`. The
failure then names the broken contract.

Assert each fact once. `assertInertia()` already shows the page rendered, so an `assertOk()` before
it adds nothing but noise.

```php
$this->actingAs(admin())->get(route('users.edit', $user))
    ->assertInertia(fn (AssertableInertia $page) => $page
        ->component('settings/users/edit')
        ->where('user.email', $user->email));
```

Assert the component and the props, never rendered HTML.

## Format expectations

Pest 5's format expectations (`toBeEmail()`, `toBeUrl()`, `toBeUuid()`, `toBeUlid()`,
`toBeIpAddress()`, `toBeJson()`, `toBeSlug()`) give clearer failures than a regex, and each has a
`not` form.

## Assert a known value

Write the expected value in the test, take it from a golden vector, or compute it by a *different*
method. Computing it with the implementation's own logic passes when that logic is wrong.

```php
// Uses the implementation's logic
$expected = now()->subHours(24)->floorSeconds(30)->toJson();
expect($from)->toBe($expected);

// Fixed input, known answer
travelTo('2025-01-01 00:00:00');
expect($from)->toBe('2024-12-31T00:00:00.000000Z');
```

### UI text

An expected message, label or status name comes from the requirement's wording or the translation
catalogue — never from the string in the code you just wrote. Resolve it through the catalogue so
the test follows the active locale:

```php
->assertInvalid(['email' => __('This email address belongs to a deleted user. Restore that user instead.')]);
```

Where a requirement quotes the German wording, the `de.json` entry must be that wording verbatim,
and a test may assert it literally.

## Assert the complete result

A status is not the result of a write. Assert everything the operation changes:

- the response or return value,
- the database state,
- the jobs and events dispatched,
- the notifications and mail sent.

On the failure path, assert that none of these happened. A test that asserts only a redirect passes
when nothing was saved.

```php
Notification::fake();

$this->actingAs(admin())->post(route('users.store'), $payload)
    ->assertRedirect(route('users.index'));

$user = User::firstWhere('email', 'nora@example.com');
expect($user?->assignedRole())->toBe(Role::User);
Notification::assertSentTo($user, UserInvitation::class);
```
