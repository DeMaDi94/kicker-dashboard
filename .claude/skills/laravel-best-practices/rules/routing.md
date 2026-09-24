# Routing

One route per action, pointing at that action's invokable controller
(`app/Http/{Area}/{Action}/{Action}Controller.php`). No resource controllers, no
`Route::resource()`, no multi-method controllers — the starter kit's `app/Http/Controllers/Settings`
is scaffolding, not the pattern (`.claude/rules/architecture.md`). The controller stays at three or
four lines: take the request, call the service, return `Inertia::render(…)` or a redirect.

```php
// routes/settings.php
Route::patch('settings/users/{user}', UpdateUserController::class)
    ->can(Permission::UpdateUsers->value)
    ->name('users.update');
```

## Name every route

Every route has a name (dotted, `{resource}.{action}`) — Wayfinder generates the TypeScript
functions the React side uses from it (`@/routes/…`) and from the controller
(`@/actions/App/Http/{Area}/{Action}/{Action}Controller`). The frontend never builds a URL string;
changing a URI then breaks `tsc` instead of a link. After adding or renaming a route, the Vite dev
server regenerates the Wayfinder files (or run `php artisan wayfinder:generate`).

Server side, redirect by name: `to_route('users.index')`.

## Use implicit route model binding

Let Laravel resolve the model from the parameter when the default lookup and 404 fit.

```php
final class ShowProjectController
{
    public function __invoke(Project $project): Response
    {
        return Inertia::render('project/show', ShowProjectProps::from($project));
    }
}
```

A missing record is then a 404 automatically — and the feature test asserts it
(`assertNotFound()`). Soft-deleted models are excluded from binding unless the route says
`->withTrashed()`, as the restore route does (B15).

## Scope nested bindings

When a nested resource must belong to its parent, scope the binding. It constrains resolution; it
does not replace authorization.

```php
Route::get('projects/{project}/tasks/{task}', ShowTaskController::class)
    ->scopeBindings()
    ->name('projects.tasks.show');
```

## Authorization on the route

Gate the route by permission with `->can(Permission::X->value)` (B13) — never by a role, and never
by an `if` in the controller. A rule richer than a permission is a domain guard called from the
service (see [`security.md`](security.md)).

## Middleware

Configured in `bootstrap/app.php` (Laravel 13 slim skeleton — no `Kernel.php`). Throttle
abuse-prone routes with a named limiter; the limit is a decision.

## Inspecting routes

`php artisan route:list` (add `--path=settings` or `--name=users` to narrow it) shows URI, name,
controller and middleware.
