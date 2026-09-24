---
paths:
  - '**/*.php'
---

# PHP style (PHP 8.5)

- `declare(strict_types=1);` at the top of every file in `app/` and `tests/` you write. (The
  starter kit's own files predate the rule; add it when you touch one.)
- Constructor property promotion: `public function __construct(private Catalogue $catalogue) {}`.
- Explicit return and parameter types on every method. `final` by default on domain classes.
- Never a fully-qualified class path in code or PHPDoc (`\Illuminate\Support\Carbon`) — import it
  and use the short name, including in `@property`, `@method` and `@return`. On a collision, alias.
- Array-shape types in PHPDoc: `@return array{id: string, cents: int}`. `list<T>` when the keys
  are sequential — PHPStan distinguishes it from `array<int, T>` and will tell you.
- `camelCase` for variables, parameters and properties — the Laravel default. `snake_case` stays
  for database columns, migration names and Eloquent attributes, which is where the framework puts
  it.
- Curly braces on every control structure, even single-line bodies.
- Prefer a PHPDoc block over inline comments. An inline comment earns its place only for a
  non-obvious *why* — most often, which requirement or decision a value comes from.
- User-facing strings go through `__('…')` — see `.claude/rules/i18n.md`.
- PHP 8.4/8.5 idioms where no Collection is in play: `array_find`, `array_find_key`, `array_any`,
  `array_all`, `array_first`, `array_last`; `new Foo()->bar()` without wrapping parentheses; the
  pipe operator `|>` over nested calls; `clone($readonly, ['prop' => $value])` to derive a changed
  readonly value object — the natural shape for a domain value in `app/Domain`.
- Style is Laravel's Pint preset, applied automatically after every edit and checked by the Stop
  gate. Do not hand-format.

## PHPStan

Level 7, pinned in `phpstan.neon` — do not pass `--level` on the command line, or the flag
quietly becomes the truth instead of the config.

**There is no baseline file and must not be one.** `--generate-baseline` is blocked by a hook.
Fix the error; as a genuine last resort use `@phpstan-ignore-next-line` with a comment saying why.
Never widen a type, add `mixed`, or cast to silence a finding — the finding is usually right.

Raise the level by fixing what the next level reports, not by exempting files.
