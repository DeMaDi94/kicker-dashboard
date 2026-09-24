# Security tests

Test every boundary where user input affects authorization, rendered output or query
construction — a defect there is hard to notice because the feature keeps working.

Write a test for each that applies:

- **Another owner's record,** where a requirement restricts records to their owner. See
  [`endpoint-tests.md`](endpoint-tests.md) for `404` over `403`.
- **Each role lacking the permission.** A dataset over the roles the endpoint must refuse (B13 —
  the check is by permission, the dataset is by role).
- **Guard rules.** Self-targeting and last-admin cases (B15) — in the domain unit test for the
  matrix, one feature case per endpoint.
- **Escaping user content** in mail and anything the server renders. Include names and every
  free-text field a template shows. Assert the dangerous characters are escaped and the raw value is
  absent. Do not assert an exact entity for a quote — Markdown and mail CSS inliners may decode it.
  (React escapes what it renders; a `dangerouslySetInnerHTML` needs its own frontend test.)
- **Injection through dynamic query parts** — sort columns, sort directions, filter fields. Send a
  value outside the allow-list and assert it is rejected or ignored, not passed to SQL.
- **An unexpected key** in a payload. A merge that accepts every key can set an attribute the user
  must not control (a `role` on a profile update, an `email` on B16's admin edit, which changes name
  and role only).

```php
it('escapes the project name in the share mail', function () {
    $project = Project::factory()->make(['name' => "O'Reilly <script>alert('xss')</script>"]);

    $mail = (new ProjectShared($project))->toMail(User::factory()->make())->render();

    expect((string) $mail)
        ->toContain('&lt;script&gt;')
        ->not->toContain("<script>alert('xss')</script>");
});

it('B16 · rejects a sort column outside the allow-list', function () {
    $this->actingAs(admin())->get(route('users.index', ['sort' => 'password']))
        ->assertInvalid(['sort']);
});
```

`ListUsersRequest` validates `sort` against `ListUsersQuery::sorts()`, so an unknown column is a
validation error. Read what the code does with an out-of-list value before asserting it; if that is
not what a requirement says, report it rather than asserting it.

Laravel defends against mass assignment, unauthorized access and unescaped output; the tests prove
the application applies the defence to each attribute, route and template.
