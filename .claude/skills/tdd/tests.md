# Good and Bad Tests

## Good Tests

**Integration-style**: Test through real interfaces, not mocks of internal parts.

```php
// GOOD: Tests observable behavior, cites the requirement
it('INV-12 · confirms checkout of a valid cart', function () {
    $this->actingAs(member())
        ->post(route('checkout.store'), ['cart' => $cart->id])
        ->assertRedirect(route('orders.show', 1));

    $this->get(route('orders.show', 1))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('orders/show')
            ->where('order.status', 'confirmed'));
});
```

Characteristics:

- Tests behavior users/callers care about
- Uses public API only
- Survives internal refactors
- Describes WHAT, not HOW
- One logical assertion per test

## Bad Tests

**Implementation-detail tests**: Coupled to internal structure.

```php
// BAD: Tests implementation details
it('calls the payment service', function () {
    $payment = Mockery::mock(ChargePaymentService::class);
    $payment->shouldReceive('__invoke')->once()->with(1500);
    app()->instance(ChargePaymentService::class, $payment);

    $this->post(route('checkout.store'), ['cart' => $cart->id]);
});
```

Red flags:

- Mocking internal collaborators
- Testing private methods
- Asserting on call counts/order
- Test breaks when refactoring without behavior change
- Test name describes HOW not WHAT
- Verifying through external means instead of interface

```php
// BAD: Bypasses interface to verify
it('saves the user to the database', function () {
    $this->post(route('users.store'), ['name' => 'Alice', /* … */]);

    expect(DB::table('users')->where('name', 'Alice')->exists())->toBeTrue();
});

// GOOD: Verifies through interface
it('B14 · lists a created user', function () {
    $this->post(route('users.store'), ['name' => 'Alice', /* … */]);

    $this->get(route('users.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('users.data.0.name', 'Alice'));
});
```

**Tautological tests**: Expected value restates the implementation, so the test passes by construction.

```php
// BAD: Expected value is recomputed the way the code computes it
it('sums line items', function () {
    $items = [new LineItem(10), new LineItem(5)];
    $expected = array_sum(array_map(fn ($i) => $i->price, $items));

    expect(InvoiceTotal::of($items))->toBe($expected);
});

// GOOD: Expected value is an independent, known literal
it('INV-07 · sums line items', function () {
    expect(InvoiceTotal::of([new LineItem(10), new LineItem(5)]))->toBe(15);
});

// BETTER: Expected values are reference answers the business already trusts
it('INV-07 · matches the reference totals', function (array $case) {
    expect(InvoiceTotal::of($case['items']))->toBe($case['total']);
})->with(goldenVectors('invoice-totals'));
```

## Frontend

The same rules hold in `vp test`: drive the component or hook through what the user or caller sees.

```ts
import { describe, expect, it } from 'vitest';
import { formatNumber } from './number';

describe('INV-07 · amounts in the active locale', () => {
    it('groups thousands', () => {
        expect(formatNumber(1234.5, 'de')).toBe('1.234,5');
    });
});
```
