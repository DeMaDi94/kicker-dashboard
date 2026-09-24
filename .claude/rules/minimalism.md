---
paths:
  - 'app/**'
  - 'resources/js/**'
---

# Least code that works

Walk this ladder in order and stop at the first rung that solves the problem:

1. **Write nothing.** The requirement may already be met.
2. **Reuse what this repo has.** The `components/core` primitives, the shell in `layouts/shell`,
   the shadcn components in `components/ui`, `sonner`, `@dnd-kit`, `date-fns`, the value objects in
   `app/Domain/Shared`. Look before you build.
3. **Use the framework.** Laravel, Eloquent, Collections and Inertia cover more than they appear to.
4. **Use the platform.** The PHP standard library, native HTML (`<details>`, `<dialog>`, form
   validation attributes, `<input type="date">`) and CSS before a component or a hook.
5. **Add a dependency** only if it is already installed. A new one needs approval.
6. **Only then** write the smallest implementation that satisfies the requirement in front of you.

## Never trade these away to be "lean"

Validation, the requirement citation, the status-ledger update, the translation keys and the test
are not optional scope. Nor are authorization, error handling that keeps the app usable, and
accessibility (labels, keyboard operation, `activatableProps()`). Cutting any of them is a defect,
not a simplification.

## What is not over-engineering here

A reviewer — or `/simplify` — measuring this code against a generic "least code" yardstick will
flag things that are the house shape. They stay:

- The requirement id in a comment or a test description.
- `t('…')` / `__('…')` around every user-facing string, and the key in every locale file.
- The controller + `{Action}Request` + `{Action}Service` split, and a `Port` between areas.
- A rule in `app/Domain` rather than inline in the service, even with one caller today.
- A `{Screen}Props` shape instead of passing the model.
- Using a `components/core` primitive where a few inline lines would also work.

## Signals you went too far up the ladder

- An abstraction with exactly one caller.
- A parameter, config key or flag nothing sets.
- A layer that only forwards to the next layer.
- Generality for a second case that does not exist yet.
- A "flexible" implementation of a rule the spec states exactly. When a requirement says a value
  is fixed ("at most 3", "fixed, not configurable"), parameterising it is not foresight, it is an
  untested code path.
