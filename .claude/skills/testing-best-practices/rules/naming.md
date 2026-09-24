# Naming and structure

File layout (mirrored, `{ClassName}Test.php`, fixtures in `tests/Fixtures/`) is fixed by
`.claude/rules/testing.md`. Move large literals out of the test body into a fixture.

## `it()` or `test()`

Match the other files in the directory. Otherwise:

- `it()` for behaviour, named as a verb phrase — the default for domain and feature tests.
- `test()` for a declarative fact: the permissions a role holds, an enum's cases, a props shape.

One declaration style per file.

## Group by requirement

A `describe()` block names the requirement or decision it covers; the cases inside state the
behaviour. That is where `spec:coverage` finds the citation.

```php
describe('INV-04 · invoice numbers', function () {
    it('starts at 0001 in a new year', function () { … });
    it('never reuses a cancelled number', function () { … });
});

describe('B15 · deleting a user', function () {
    it('refuses to delete the last active admin', function () { … });
});
```

Use a separate `describe()` per requirement when one file covers several. Do not add a `describe()`
that neither groups a requirement nor makes the file easier to read, and do not use one for cases
that differ only in input — that is a dataset.

## Name the behaviour

The name is a specification: the result, and the condition that causes it.

- Name the behaviour, not the method — the file already names the class.
- No `Given`/`When`/`Then`.
- Verbs that state a result: `returns`, `renders`, `creates`, `sends`, `rejects`, `forbids`,
  `redirects`, `falls back`, `does not`.

```php
it('forbids a user without users.update from editing a user', function () { … });
it('shows only deleted users when the deleted filter is on', function () { … });
it('falls back to the English key when a locale has no entry', function () { … });
```

Not `it('works correctly')`, `it('returns data')` or `it('handle creates record')`.
