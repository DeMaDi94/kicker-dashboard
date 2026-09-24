# Endpoint tests

`.claude/rules/testing.md` fixes the required feature-test cases: **success**, **validation**
(`assertInvalid()`) wherever an `{Action}Request` exists, **not found** wherever the route takes a
resource id, and **refused** wherever the route sits behind a permission. This file is how to write
them well.

Request helpers and response assertions are in
`vendor/laravel/framework/src/Illuminate/Foundation/Testing/Concerns/MakesHttpRequests.php` and
`.../Testing/TestResponse.php`. Call routes by name (`route('users.update', $user)`), never by a
hand-written URI.

## Coverage per endpoint

Write each case that applies:

- **Signed out.** A browser route redirects to the login (`assertRedirect(route('login'))`), it does
  not return `401`.
- **Missing permission.** A signed-in user without the route's permission gets `assertForbidden()`.
- **Someone else's record,** where a requirement restricts records to their owner (see below).
- **Route constraint.** A soft-deleted model on a route without `->withTrashed()` is
  `assertNotFound()`; an unscoped nested binding is `assertNotFound()`.
- **Invalid input.** `assertInvalid([...])` and nothing persisted.
- **Valid input.** The response *and* the persisted state and side effects
  ([`assertions.md`](assertions.md)).

## Authorization: permission matrix below, one refusal above

Code checks permissions, never role names (B13). A feature test shows the endpoint *performs* the
check; it cannot tell which mechanism refused (route `can`, a Request's `authorize()`, a guard in
the service — all return 403).

- Which role holds which permission is asserted once, against the domain's definition
  (`tests/Feature/Users/RolesAndPermissionsTest.php`), not re-proved per endpoint.
- A rule richer than a permission — "not your own account", "not the last admin" (B15) — is a
  domain guard (`App\Domain\Users\AccountGuard`); its matrix is a unit test in
  `tests/Unit/Domain/Users/`.
- Each endpoint then needs **one** refused case (a `member()` hitting an admin route) and one case
  per guard rule proving the service calls it.

```php
it('B13 · forbids a user without users.delete', function () {
    $this->actingAs(member())->delete(route('users.destroy', admin()))
        ->assertForbidden();
});
```

## Records that belong to someone

Where a requirement limits a record to its owner, request another owner's record and assert the
status the requirement implies. Prefer `404` over `403` when one user must not learn that another's
record exists — `403` confirms it does. Which of the two is a requirement or decision; ask if the
spec is silent.

## Validation

- One test per rule when each failure is a separate contract (usually: each rule a requirement
  states).
- One test with an empty payload asserts all required fields at once.
- Assert the message the user reads, resolved through `__()` — a present but wrong message is a
  defect.
- A dataset for invalid inputs that share setup and assertions:

```php
it('rejects an invalid role', function (mixed $role) {
    $this->actingAs(admin())->post(route('users.store'), [
        'name' => 'Nora Neu',
        'email' => 'nora@example.com',
        'role' => $role,
    ])->assertInvalid(['role']);

    $this->assertDatabaseMissing('users', ['email' => 'nora@example.com']);
})->with([
    'unknown role' => ['owner'],
    'empty' => [''],
    'not a string' => [['admin']],
]);
```

Send the invalid value through the endpoint and assert the error. Asserting that `rules()` contains
a string tests the declaration, not the behaviour — only for a rule no request can reach, with the
reason in the test.

### Which layer owns which case

A domain rule (or a rule object) owns the matrix of passing and failing values in its unit test.
The endpoint test proves the endpoint applies it and the user gets the message — one case. When
both hold the matrix, move it down and keep one case above. Never remove that last case: the unit
test still passes if the request forgets the rule. The same split applies to guards and scopes.
