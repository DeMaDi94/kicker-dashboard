---
name: create-screen
description: Scaffold an Inertia screen — the invokable controller, its Request and its Service under app/Http/{Area}/{Action}/, the route, the React page under resources/js/pages/{area}/, its props type, the translation keys, and the feature and mirrored tests. Use when adding a new page, list, detail view, modal flow or form to the app.
---

# Create a screen

The shape is fixed so that screens stay comparable and the architecture tests keep passing. Read a
sibling screen before starting; match what is already there over what this file describes.

## 0. Name it from the spec, not from scratch

The **area** is one of the catalogue's own areas — `docs/GLOSSARY.md` maps prefix → namespace. The
**action** is `{Verb}{Thing}`: `ShowProject`, `StoreInvoice`, `UpdateCatalog`. Read the
requirements the screen implements first — the whole area section, plus any navigation
requirements saying where the screen sits.

Check whether an existing screen already covers it. Extending one is usually right.

## 1. `app/Http/{Area}/{Action}/{Action}Controller.php`

Single-action, invokable, and only the HTTP edge — take the Request, call the service, render:

```php
final class ShowProjectController
{
    public function __invoke(Project $project): Response
    {
        return Inertia::render('project/show', [
            'project' => ProjectProps::from($project),
        ]);
    }
}
```

No Eloquent writes, no transaction, no branching on domain state. Never pass a raw Eloquent model
as a prop — the React side would then depend on column names.

## 2. `{Action}Request.php` — only when there is input

A Laravel `FormRequest` (not Spatie Data; it is not installed). Skip the file entirely for a route
that only takes route parameters. Custom messages go through `__()`, worded as the requirement
words them where it does.

## 3. `{Action}Service.php` — when the action does more than render

A `final` class, `declare(strict_types=1)`, **one public method** (`__invoke`), dependencies through
the constructor. It owns the orchestration: the transaction, the model reads and writes, the audit
entry, and calling the domain rules and persisting what they return.

```php
final class StoreProjectService
{
    public function __construct(private ProjectNumbers $numbers) {}

    public function __invoke(int $userId, StoreProjectInput $input): Project
    {
        return DB::transaction(function () use ($userId, $input): Project { … });
    }
}
```

- **Never takes a `Request`.** Inputs arrive as typed arguments or a small readonly input object, so
  the service is callable from a test or a console command without faking HTTP.
- **Skip the file** for a read screen that just renders props from a model — a pass-through service
  with no content is worse than none. Write actions, anything in a transaction and anything writing
  an audit entry always get one.
- It orchestrates, it does not decide. An amount, a date, a status or a derived name computed inside
  a service is a business rule in the wrong layer — it belongs in `app/Domain/{Area}/`, and the
  service calls it.
- **Own area only.** A service may not import another area's service. If this screen needs work
  another area owns, that area publishes it as a `Port` in its `app/Http/{OtherArea}/Ports/` folder
  and you inject the Port. Before writing one, check the need is not simply a domain rule —
  `app/Domain/` is importable from anywhere and needs no Port.
  `tests/Architecture/AreaBoundariesTest.php` fails the build otherwise.
- Orchestration two actions in **this** area both need goes in `app/Http/{Area}/Shared/`.

## 4. Route

`routes/web.php`, named, grouped by area, inside the `auth` group unless the requirement says
otherwise. Use the name everywhere — never a hardcoded path. Wayfinder generates the TypeScript
helpers from these (`@/routes/…`, `@/actions/…`), so the React side gets them for free.

## 5. `resources/js/pages/{area}/{screen}.tsx`

Thin: takes props, composes. The work lives in `resources/js/features/{area}/`.

```tsx
type ShowProjectProps = { project: Project };

export default function ShowProject({ project }: ShowProjectProps) {
    const { t } = useTranslation();

    return (
        <>
            <Head title={t('Project')} />
            <PageTitle title={project.name} description={t('…')} />
            <Panel>…</Panel>
        </>
    );
}

ShowProject.layout = { view: 'Project' };
```

- Compose from `components/core/` (the behavioural primitives) and `components/ui/` (shadcn)
  before writing new markup. Do not re-solve confirm, toast, drag-reorder, autosave, undo or a
  paged list inline — `.claude/rules/react.md` lists the primitive for each.
- Every user-facing string through `t('…')`, and every new key into every `lang/{locale}.json`
  (`.claude/rules/i18n.md`). The Stop gate fails on a missing one.
- Tokens from `resources/css/theme.css`; never a raw hex or a `820px` media query.
- A screen that belongs in the navigation gets its entry in `layouts/shell/nav-items.ts`; a group
  of related screens gets a `TabGroup` passed through the page's `layout`.
- `features/{area}` may not import from another `features/{area}`. Shared things go to
  `components/core/` or `lib/`.

## 6. Tests — mirror, do not colocate

- `tests/Feature/{Area}/{Action}Test.php` — success, plus validation where a Request exists,
  not-found where the route takes an id, and refused where the route sits behind a policy or gate.
  Assert Inertia's component name and props, not HTML.
- Where a service exists, drive the edge cases through it directly (it takes no `Request`, so this
  is a plain call) and keep the feature test to the wiring — one success and one validation path.
- `resources/js/features/{area}/*.test.tsx` — only for genuine interaction (a hook, a stateful
  component). Do not unit-test a component that just renders props.
- Cite the requirement ids in the descriptions.

## 7. Status and verify

Move the requirements this screen implements in `docs/spec/status.txt`, then:

```bash
composer test && npm run check && npm run test
php artisan spec:coverage
```

`spec:coverage` is the one that catches a screen built without its requirements accounted for.
