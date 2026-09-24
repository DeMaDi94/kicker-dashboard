# Validation

## Always a FormRequest, named for the action

Every action with input gets an `{Action}Request extends FormRequest` in its own folder,
`app/Http/{Area}/{Action}/{Action}Request.php` — never inline `$request->validate()` and never a
shared request across actions. The request owns the rules and their translated messages; it writes
nothing (`.claude/rules/architecture.md`). Laravel `FormRequest`, not Spatie Data.

```php
final class StoreUserController
{
    public function __invoke(StoreUserRequest $request, StoreUserService $store): RedirectResponse
    {
        $store($request->toInput());

        return to_route('users.index');
    }
}
```

`authorize()` can enforce access to the operation (by permission — B13, see
[`security.md`](security.md)). Validation establishes shape and values; it does not authorize.

## Every limit is a requirement

`max:255`, `min:8`, an allowed list, a date window — each is behaviour the user meets. Cite the
requirement or decision id beside a rule that exists because the spec says so. Where the spec gives
no limit, ask; do not invent a friendly one. Allowed values of an enum come from the enum:
`Rule::enum(Role::class)`.

## Array syntax

Array syntax composes with rule objects and closures and avoids delimiter problems. Use it.

```php
'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)->withoutTrashed()],
```

## Hand the service a typed input, not the array

Pass `validated()` data onward only through a typed input object the request builds — the service
never sees a `Request` (see `StoreUserRequest::toInput()`):

```php
public function toInput(): StoreUserInput
{
    return new StoreUserInput(
        name: $this->string('name')->toString(),
        email: $this->string('email')->toString(),
        role: Role::from($this->string('role')->toString()),
    );
}
```

Never `$request->all()`. Validated data is not automatically safe for mass assignment, and a
sensitive attribute is never added to the rules to make mass assignment convenient.

## Messages are translated

A custom message — from `messages()`, a closure rule, or `after()` — goes through `__('…')` with a
key in every `lang/{locale}.json`. Laravel's own messages live in `lang/{locale}/validation.php`
(German with „Du“ — B7). Attribute names shown in messages come from `attributes()` via `__()`.

```php
function (string $attribute, mixed $value, Closure $fail): void {
    if (User::onlyTrashed()->where('email', $value)->exists()) {
        $fail(__('This email address belongs to a deleted user. Restore that user instead.'));
    }
},
```

## Normalise before validating

`prepareForValidation()` is where input is normalised so the rules see the stored form — e.g. the
address lowercased because Fortify signs in with a lowercased address (B14).

## Conditional rules

`Rule::when()`, `required_if` or `exclude_unless` when they make the condition explicit — and the
condition itself is a requirement.

```php
'company_name' => [
    'string',
    'max:255',
    Rule::when($this->input('account_type') === 'business', ['required'], ['nullable']),
],
```

## Cross-field validation after the base rules

`after()` for checks spanning several fields or application state. Skip the expensive part when a
prerequisite field already failed.

```php
/**
 * @return list<Closure>
 */
public function after(): array
{
    return [
        function (Validator $validator): void {
            if ($validator->errors()->hasAny(['product_id', 'quantity'])) {
                return;
            }

            $stock = Product::find($this->integer('product_id'))?->stock;

            if ($stock !== null && $this->integer('quantity') > $stock) {
                $validator->errors()->add('quantity', __('Not enough stock.'));
            }
        },
    ];
}
```

Validation against mutable state does not prevent a race between validating and persisting. Enforce
uniqueness, stock and similar invariants with a constraint, an atomic update or a locked
transaction in the service. And a rule that *decides* (is this quantity allowed?) belongs in
`app/Domain/{Area}/`, called from `after()`, so it is unit-testable.
