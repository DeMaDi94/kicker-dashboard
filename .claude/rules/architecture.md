---
paths:
  - 'app/**'
  - 'routes/**'
  - 'database/**'
  - 'config/**'
---

# Backend architecture

Laravel 13, slim skeleton — `bootstrap/app.php`, **not** `app/Http/Kernel.php`. Middleware,
exceptions and routing are configured there. Do not carry `Kernel.php` habits over from a
Laravel 10 codebase.

## The layers

```
app/
├─ Domain/{Area}/          the requirements' rules — pure PHP
│  └─ Shared/              value objects and rules two or more areas use
├─ Http/{Area}/{Action}/   one invokable controller, its Request and its Service
│  └─ ../Ports/           the area's only public surface, for other areas to call
├─ Models/                 Eloquent
└─ Spec/                   requirement-catalogue tooling (not product code)
```

`{Area}` comes from the requirement catalogue's own areas — the prefix → namespace map is in
`docs/GLOSSARY.md`. Do not invent an area name.

## `app/Domain` — where the rules live

**The domain layer must run without booting Laravel.** No Eloquent, no `Request`, no facades, no
Inertia. `tests/Architecture/BoundariesTest.php` enforces it.

That constraint is not tidiness. It is what lets the rules run as fast unit tests over hundreds of
cases (golden vectors, datasets), and what stops a calculation rule from quietly acquiring a
database round-trip. The rules are the asset; everything else is plumbing.

- Value objects and pure functions. Classes are `final` and files `declare(strict_types=1)`.
- Anything used by two or more areas goes in `Domain/Shared`. Do not duplicate a rule across areas.
- **Domain rules central, HTTP orchestration colocated.** A feature's rules are usually needed by
  more than one screen (the list, the detail, the export, the mail), so a rule living inside one
  action's service would force either duplication or a shared `Services/` folder. The per-action
  service below orchestrates; it does not decide.

## `app/Http/{Area}/{Action}` — one folder per action

```
app/Http/Project/StoreProject/
├─ StoreProjectController.php   single-action, invokable — the HTTP edge
├─ StoreProjectRequest.php      only when there is input to validate
└─ StoreProjectService.php      the orchestration, only when there is any
```

The three have three different jobs, and the split exists so that each one stays testable on its
own terms:

| File | Owns | Must not |
| --- | --- | --- |
| `{Action}Controller` | the HTTP edge: take the Request, call the service, return `Inertia::render(…)` or a redirect | touch Eloquent, open a transaction, branch on domain state |
| `{Action}Request` | validation rules and their (translated) messages | write anything |
| `{Action}Service` | orchestration: transactions, Eloquent reads and writes, audit entries, calling the domain rules and persisting what they return | decide a business rule, know about `Request`, `Inertia` or a response |

- Controllers are **invokable** (`__invoke`) and stay at three or four lines. A controller with an
  `if` in it that is not a guard clause has orchestration in it that belongs in the service.
- The service is a `final` class with `declare(strict_types=1)` and **one public method**, also
  `__invoke`. Dependencies come in through the constructor; the action's inputs come in as typed
  arguments or a small readonly input object — **never a `Request`**, so the service is callable
  from a test or a console command without faking HTTP.
- A service **only when there is orchestration to do**. A read screen that renders a props object
  from a model does not get one — that would be a pass-through file with no content. Write actions,
  anything wrapped in a transaction, and anything that writes an audit entry always do.
- The service is where the database lives; the domain is where the decisions live. If a service
  starts computing an amount, a date, a status or a derived name, that rule belongs in
  `app/Domain/{Area}/` and the service should be calling it.
- Laravel `FormRequest`, not Spatie Data — that package is not installed.
- Inertia props: give each page a `{Screen}Props` shape and mirror it in the page's TypeScript.
  Never pass a raw Eloquent model as a prop; the React side then depends on column names.

```php
final class StoreProjectController
{
    public function __invoke(StoreProjectRequest $request, StoreProjectService $store): RedirectResponse
    {
        $project = $store($request->user()->id, StoreProjectInput::from($request->validated()));

        return to_route('projects.show', $project);
    }
}
```

## Crossing an area: a Port, never another area's service

**A service may depend on services in its own area only.** Reaching into
`App\Http\{OtherArea}\{Action}\{Action}Service` couples two areas to each other's internals, and
the coupling is invisible until one of them changes.

When an action genuinely needs work another area owns, the owning area publishes a **Port**:

```
app/Http/Billing/Ports/
└─ InvoiceTotalsPort.php      what Billing lets other areas ask of it
```

```php
final class InvoiceTotalsPort
{
    public function __construct(private InvoiceTotalsService $totals) {}

    public function forProject(Project $project): InvoiceTotals
    {
        return $this->totals->forProject($project);
    }
}
```

- A Port is a `final` class with `declare(strict_types=1)` — a thin delegation to the area's own
  services, nothing else. Not an interface: there is one implementation and a container binding
  would only add indirection.
- It lives in the **owning** area (`app/Http/Billing/Ports/`), not the calling one, and it is the
  **only** thing in that area another area may import. Everything else behind it is private.
- Its signature is the contract: domain value objects, models or scalars in and out. **Never a
  `Request`**, never an array shape the caller has to guess, never `mixed`.
- Adding a method to a Port is a deliberate widening of what an area promises. Prefer one narrow
  method per genuine need over a Port that mirrors a whole service.

`tests/Architecture/AreaBoundariesTest.php` enforces this. The pattern without a test leaks in
exactly the places nothing checks.

**Most cross-area needs are not Port-shaped at all.** A calculation, a format or a derivation is a
*rule*, and rules live in `app/Domain/{Area}/` (or `Domain/Shared`), which any area may import
freely — that is the whole point of the domain layer. Reach for a Port only when the other area has
to *do* something: allocate a number, write an audit entry, call an external service. If you are
about to write a Port around a pure function, the function is in the wrong layer.

When two actions **in the same area** need the same orchestration, it goes in
`app/Http/{Area}/Shared/` — an ordinary service, no Port needed, not visible to other areas. (Not
to be confused with `app/Domain/Shared/`, which is cross-area *rules*.)

The starter kit's `app/Http/Controllers/Settings` (and `Middleware`, `Requests`) stay as they are.
They are scaffolding, and they are not the pattern to copy.

## Models

- Casts in a `casts()` method, not a `$casts` property.
- Type-hint relationships with generics: `HasMany<Invoice, $this>`.
- Enums in `app/Domain/{Area}/` next to the rules that use them, not a global `Enums/` folder.
  Their stored values are the wire format — once data exists they are not free to prettify. Their
  UI labels are translation keys, not the enum value. See `docs/GLOSSARY.md`.
- When modifying a column in a migration, restate **every** attribute it previously had; anything
  omitted is dropped.

## Configuration

- `config/` holds only genuinely fixed constants and environment wiring (endpoints, credentials,
  the supported locales).
- Anything the team can edit from a settings screen (a rate, a price, a template) is **data** — a
  shared-settings model — not config. Putting an editable default in `config/` is a bug: it
  silently stops being editable.

## External services

Call third-party APIs **server-side only**, through a small client in the owning area that
caches, rate-limits and sends an identifying User-Agent where the provider's policy expects one.
Every lookup must degrade gracefully when the provider is unreachable — a failed lookup is never an
error the user has to resolve. In tests, `Http::fake()` them; never hit the real endpoint.
