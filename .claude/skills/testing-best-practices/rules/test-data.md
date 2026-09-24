# Factories and test data

## Each test makes its own data

Create mutable records inside the test that uses them, so setup is visible and each test picks its
factory state. `beforeEach()` holds configuration only, never records.

`tests/Pest.php` provides `admin()` and `member()` — a user holding the admin or the plain user role
(B13). Use them rather than building roles by hand.

## Constructing records

- `create()` when the test needs the row; `make()` only when it does not (rendering a notification,
  a model passed to a pure function).
- A named factory state over a raw attribute: `User::factory()->unverified()->create()` says what
  the data means; `create(['email_verified_at' => null])` only says its value. The repo's
  `withRole(Role::Admin)` is such a state.
- `for()` or the relationship helper to declare the owner; `recycle()` when several records share one
  parent; `sequence()` when they need different attributes.

```php
$project = Project::factory()->for($owner)->create();

$tasks = Task::factory()
    ->count(3)
    ->recycle($project)
    ->sequence(['position' => 1], ['position' => 2], ['position' => 3])
    ->create();
```

Create only the records that arrange the behaviour or support an assertion. A factory's defaults
are test scaffolding — they are never evidence of a business default.

## Golden vectors before invented inputs

Where a rule has a reference answer set, freeze it in `tests/Fixtures/{name}.json` as
`{"cases": [{…}]}` and run the rule over it with `goldenVectors()` (`.claude/rules/testing.md`).
Each case reaches the test as one array.

```php
describe('INV-07 · rounding', function () {
    it('matches the reference rounding', function (array $case) {
        expect(Rounding::of($case['in']))->toBe($case['out']);
    })->with(goldenVectors('rounding'));
});
```

## Datasets

A dataset when setup, body and assertions stay the same across inputs. Derive it from the domain
(`Role::permissions()`) rather than hard-coding which roles are refused, so a new role is covered
without editing the test.

- enum cases,
- roles lacking a permission,
- boundary values the requirement states,
- inputs that are invalid in the same way,
- input/output pairs.

```php
it('B13 · forbids every role without users.view', function (Role $role) {
    $this->actingAs(User::factory()->withRole($role)->create())
        ->get(route('users.index'))
        ->assertForbidden();
})->with(fn () => array_values(array_filter(
    Role::cases(),
    fn (Role $role) => ! in_array(Permission::ViewUsers, $role->permissions(), true),
)));
```

Name each case by its difference (`'empty' => ['']`), so a failure identifies it without counting
positions.

Write separate tests when cases need different setup, behaviour or assertions. A test with a branch
in its body is two tests in one.

Boundary values come from the requirement ("at most 3" → 3 passes, 4 fails). Do not invent a
boundary the spec does not state.
