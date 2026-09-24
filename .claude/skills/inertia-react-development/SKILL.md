---
name: inertia-react-development
description: Inertia 3 + React 19 client patterns in this repo's house idiom — pages and their props types, <Link> and router visits through Wayfinder, <Form> and useForm, useHttp for non-page JSON, deferred and optional props, WhenVisible, InfiniteScroll, prefetching, optimistic updates, instant visits, layout props, flash and once props, and live polling through useLivePoll. Load when writing or changing anything under resources/js/pages or resources/js/features that navigates, submits, reloads or reads page props, or when Inertia::render props on the server change shape. Not for shadcn styling alone (see .claude/rules/react.md) and not for the backend rules.
license: MIT
metadata:
  source: "@inertiajs/react@3.7.1 resources/boost/skills/inertia-react-development/SKILL.blade.php (+ inertiajs/inertia-laravel@v3.3.4 resources/boost/guidelines/core.blade.php for the server side)"
  adapted: "Blade flattened to the v3 React branch; every snippet rewritten to Wayfinder routes, t() text, design tokens and typed props; polling via useLivePoll (B9), list state via useListQuery, confirm/toast via the core primitives; useHttp limited to non-page JSON (B1); Boost MCP lookups replaced by reading the package sources."
---

# Inertia React development

`.claude/rules/react.md` and `.claude/rules/i18n.md` are the contract; this file is how Inertia
is used inside it. Where the upstream Inertia docs and the harness disagree, the harness wins.

The ground rules (decision B1): the server owns routing and data. A page gets its props from an
invokable controller and renders them. There is no client router, no data-fetching library and no
`fetch`-in-an-effect for page data. To get fresher page data, ask Inertia for the props again
(a partial reload), never around it.

Snippets from the Users area are real code in this repo. Names from anywhere else
(`ToggleItemController`, `LookupController`, `ChecklistProps`, `activity`, `entries`) are
placeholders — the real names come from the requirement being implemented and `docs/GLOSSARY.md`.

## Where things go

- `resources/js/pages/{area}/{screen}.tsx` — the Inertia page, kebab-case, thin: take props,
  compose. The component name passed to `Inertia::render()` is the path under `pages/`
  (`'settings/users/index'`).
- `resources/js/features/{area}/` — that area's components and hooks. One feature area never
  imports another; shared behaviour goes to `components/core/` or `lib/`.
- The props type is `{Screen}Props`, colocated with the page. Shared entity shapes live in
  `features/{area}/types.ts` or `types/`.
- Layouts are resolved in `resources/js/app.tsx` by page-name prefix (`auth/` → auth layout,
  `settings/` → app + settings layout, everything else → app layout). A page does not wrap itself
  in a layout.

Look at `resources/js/pages/settings/users/{index,create,edit}.tsx` before writing a page — they
are the house idiom.

## A page

```tsx
import { Head } from '@inertiajs/react';
import { Panel } from '@/components/core/panel';
import Heading from '@/components/heading';
import type { UserRow } from '@/features/users/types';
import { UserTable } from '@/features/users/user-table';
import { useTranslation } from '@/hooks/use-translation';
import { i18nKey } from '@/lib/i18n';
import { index } from '@/routes/users';

type UsersIndexProps = {
    users: UserRow[];
};

export default function UsersIndex({ users }: UsersIndexProps) {
    const { t } = useTranslation();

    return (
        <>
            <Head title={t('Users')} />
            <Heading variant="small" title={t('Users')} />
            <Panel>
                <UserTable users={users} />
            </Panel>
        </>
    );
}

UsersIndex.layout = {
    breadcrumbs: [{ title: i18nKey('Users'), href: index() }],
};
```

- Props are typed; never `any`, never `as`. `noUncheckedIndexedAccess` is on — handle
  `T | undefined` rather than asserting it away.
- The static `layout` object is written outside a render, so its text is marked with `i18nKey()`
  and the layout calls `t()` on it. That is the only computed `t()` allowed.
- Shared props (`auth`, `locale`, `i18n`, …) are typed once in `resources/js/types/global.d.ts`;
  read them with `usePage().props`. Gate UI on a permission, never on a role:
  `auth.permissions.includes('users.view')` (B13).

## Navigation

URLs come from Wayfinder, never a string literal. Two generated trees:

- `@/routes/{name}` — named routes: `import { create, edit, index } from '@/routes/users'`.
- `@/actions/App/Http/{Area}/{Action}/{Action}Controller` — the invokable controller, a **default**
  import: `import UpdateUserController from '@/actions/App/Http/Users/UpdateUser/UpdateUserController'`.

Both return a route definition (`{ url, method }`) that `<Link>`, `router.*`, `<Form>` and
`useForm` all accept directly; `.url()` gives the string, `.form()` the `<Form>` attributes.
The Vite plugin regenerates both on route changes; without the dev server running, use
`php artisan wayfinder:generate --with-form`.

```tsx
import { Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { create, edit } from '@/routes/users';

<Button asChild>
    <Link href={create()}>{t('Create user')}</Link>
</Button>

<Link href={edit(user.id)} prefetch>
    {user.name}
</Link>

router.visit(edit.url(user.id));
router.post(passwordResetLink(user.id), {}, { preserveScroll: true, onError });
router.delete(destroy(user.id), { onError });
```

- `prefetch` on a `<Link>` prefetches on hover; use it on the entries a user is likely to open.
- A `<Link>` with a non-GET method renders as a button (`as="button"`) — prefer a
  `<Button>` that calls `router.post(…)` when it needs the house button styling.
- A destructive visit is confirmed with `useConfirm()` from `@/components/core/dialogs`, never
  `window.confirm`. A failed visit's first error goes to `toast(message, 'err')` from
  `@/components/core/toast` — see `pages/settings/users/edit.tsx` for the `onError` shape.
- Every visit that replaces an earlier one (a filter change, a search) cancels what is in flight
  with `router.cancelAll()` — `useListQuery()` already does.

## Forms

### `<Form>` — the default

```tsx
import { Form } from '@inertiajs/react';
import StoreUserController from '@/actions/App/Http/Users/StoreUser/StoreUserController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

<Form {...StoreUserController.form()} className="space-y-6">
    {({ processing, errors }) => (
        <>
            <div className="grid gap-2">
                <Label htmlFor="name">{t('Name')}</Label>
                <Input id="name" name="name" required autoComplete="off" />
                <InputError message={errors.name} />
            </div>

            <Button disabled={processing}>{t('Create user')}</Button>
        </>
    )}
</Form>
```

- A route with a parameter passes it to `.form()`: `UpdateUserController.form(user.id)`.
  Wayfinder spoofs PATCH/PUT/DELETE through `_method`.
- Visit options go in `options`: `options={{ preserveScroll: true }}`.
- The render function also receives `hasErrors`, `progress`, `wasSuccessful`,
  `recentlySuccessful`, `isDirty`, `defaults`, `reset`, `clearErrors`, `resetAndClearErrors`
  and `submit`.
- `resetOnSuccess`, `resetOnError` (each `true` or a list of field names) and
  `setDefaultsOnSuccess` reset the form automatically; `disableWhileProcessing` and
  `cancelOnUnmount` exist too.
- Success feedback is a flash toast from the controller (`Inertia::flash('toast', …)`, picked up
  by `hooks/use-flash-toast.ts`), not a `wasSuccessful` banner the page invents.

### `useForm` — when state is driven programmatically

Use it when the form's values are set from code (a picker that fills three fields, a draft saved
by `useAutosave()`), not just typed into named inputs.

```tsx
import { useForm } from '@inertiajs/react';
import UpdateUserController from '@/actions/App/Http/Users/UpdateUser/UpdateUserController';

const form = useForm({ name: user.name, role: user.role });

const submit = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    form.submit(UpdateUserController(user.id), { preserveScroll: true });
};

<form onSubmit={submit}>
    <Input
        value={form.data.name}
        onChange={(event) => form.setData('name', event.target.value)}
    />
    <InputError message={form.errors.name} />
    <Button disabled={form.processing}>{t('Save')}</Button>
</form>
```

## Page data over time

### Polling — `useLivePoll()`, never `usePoll`

```tsx
import { useLivePoll } from '@/components/core/use-live-poll';

useLivePoll(['users', 'pagination']);
```

A partial reload of the named props every 6 s while the tab is visible (B9). Inertia's
`usePoll` only throttles a background tab, so call sites never use it directly. A screen that
needs another interval does not pass one — the primitive changes, with a `D*` decision id.

### List search, filters, page — `useListQuery()`

Search, filters and page live in the URL, typing is debounced 300 ms (B9), and every change
cancels the visit in flight. Use it with `<ListPager>`; see `pages/settings/users/index.tsx`.
Never hand-roll a `router.get` with a `setTimeout`.

### Deferred props

Server: `'stats' => Inertia::defer(fn () => …)` (optionally a group name as the second argument).
Client: the prop is `undefined` on first render, so type it optional and give it a skeleton.

```tsx
import { Deferred } from '@inertiajs/react';
import { Skeleton } from '@/components/ui/skeleton';

type DashboardProps = { stats?: DashboardStats };

<Deferred data="stats" fallback={<Skeleton className="h-4 w-3/4" />}>
    {stats && <StatsPanel stats={stats} />}
</Deferred>
```

### `WhenVisible` — load a prop when it scrolls into view

```tsx
import { WhenVisible } from '@inertiajs/react';

<WhenVisible data="activity" buffer={200} fallback={<Skeleton className="h-24" />}>
    {({ fetching }) => <ActivityList items={activity ?? []} refreshing={fetching} />}
</WhenVisible>
```

The server sends the prop as `Inertia::optional(fn () => …)` so it is only resolved when asked
for. `Inertia::lazy()` / `LazyProp` are gone in v3 — `optional()` replaces them.

### `InfiniteScroll`

```tsx
import { InfiniteScroll } from '@inertiajs/react';

<InfiniteScroll data="entries">
    {entries.data.map((entry) => <EntryRow key={entry.id} entry={entry} />)}
</InfiniteScroll>
```

Needs `Inertia::scroll($paginator)` on the server. Only where a requirement asks for an endless
list — paged lists use `useListQuery()` + `<ListPager>`.

### Once and flash

- `Inertia::once(fn () => …)` sends a prop once and the client keeps it across visits — the
  shared `i18n` catalogue is the example (`HandleInertiaRequests`).
- `Inertia::flash('toast', ['type' => …, 'message' => __('…')])` carries a one-off message to the
  next page; `use-flash-toast.ts` turns it into a `toast()`.

## Visits that feel instant

### Optimistic updates

The page shows the change before the server answers; a failure rolls it back.

```tsx
router
    .optimistic<ChecklistProps>((props) => ({
        items: props.items.map((item) =>
            item.id === id ? { ...item, done: !item.done } : item,
        ),
    }))
    .post(ToggleItemController(id), {}, { preserveScroll: true });
```

`<Form optimistic={(props, data) => ({ … })}>` and `useForm(…).optimistic(…)` work the same way.
The server remains authoritative (it re-sends the props); do not mirror a domain rule client-side
to compute the optimistic value unless that mirror is pinned (see `.claude/rules/react.md`).

### Instant visits

The target component renders at once with the shared props (and any `pageProps` given), while its
own props load in the background:

```tsx
<Link href={index()} component="settings/users/index">
    {t('Users')}
</Link>
```

`instant` without `component` works only when the route definition names its component; the
Wayfinder helpers generated here do not, so pass `component` explicitly. The page must render
sensibly with its own props missing (skeletons, as for deferred props).

### Layout props

Static layout props go on the page (`Page.layout = { breadcrumbs: […] }`, above). A value known
only at render — a breadcrumb with the record's name, an auth screen's title — is set with
`setLayoutProps`:

```tsx
import { setLayoutProps } from '@inertiajs/react';

setLayoutProps({
    title: i18nKey('Authentication code'),
    description: i18nKey('Enter the authentication code provided by your authenticator application.'),
});
```

The layout translates what it receives; see `pages/auth/two-factor-challenge.tsx`.
`resetLayoutProps()` clears them.

## `useHttp` — plain JSON, never page data

`useHttp` sends a request that is not a page visit and returns the JSON. It is for a genuine
side-channel — a lookup while typing, fetching a QR code (`hooks/use-two-factor-auth.ts`) — never
for loading or refreshing what the page renders (B1: that is a prop, a deferred prop or a partial
reload).

```tsx
import { useHttp } from '@inertiajs/react';

const lookup = useHttp<{ query: string }, LookupResult>({ query: '' });

const search = async (query: string) => {
    lookup.setData('query', query);
    const result = await lookup.submit(LookupController());
    setMatches(result.matches);
};
```

Type the response with the second generic instead of asserting it. `processing`, `errors`,
`cancel()` and `optimistic()` behave as on `useForm`.

## Server side (inertia-laravel 3)

- An invokable controller under `app/Http/{Area}/{Action}/` returns
  `Inertia::render('{area}/{screen}', [...])` — props are arrays or DTOs (see
  `app/Http/Users/Shared/UserRow.php`), never a raw Eloquent model.
- Prop types: `Inertia::optional()`, `Inertia::defer()`, `Inertia::merge()` / `deepMerge()`,
  `Inertia::always()`, `Inertia::scroll()`, `Inertia::once()`. They work inside nested arrays;
  `only` / `except` take dot-notation paths.
- Redirect after a write with `to_route('…')` and a flash toast (`StoreUserController`).
- Where Inertia is unclear, read the source rather than guessing:
  `node_modules/@inertiajs/react/types/*.d.ts`, `node_modules/@inertiajs/core/types/*.d.ts` and
  `vendor/inertiajs/inertia-laravel/src/`.

## v3 changes worth knowing

- Axios is gone: Inertia uses its own XHR client. Interceptors go through `http` from
  `@inertiajs/react` (`http.onRequest`, `http.onResponse`). Do not add Axios back — a new
  dependency needs approval.
- `router.cancel()` is now `router.cancelAll()`.
- Events renamed: `invalid` → `httpException`, `exception` → `networkError`
  (`router.on('httpException', …)`, `onHttpException` / `onNetworkError` visit callbacks).
- The `future` config namespace is gone; every v2 future option is always on.
- SSR runs in Vite dev through `@inertiajs/vite`; no separate Node server during development.

## Verifying

- `npm run check && npm run test` — types, lint, the frontend tests.
- `./vendor/bin/pest --filter={Screen}Test` — the feature test asserting the Inertia props.
- Server-side errors while a page misbehaves: `php artisan pail`.

## Pitfalls

- A plain `<a>` for an in-app link — a full reload. Use `<Link>`.
- A hardcoded path instead of a Wayfinder helper — breaks silently when a route moves.
- `usePoll`, `setInterval` + `router.reload`, or `fetch` in an effect for page data.
- A bare string or a computed key in `t()`; a raw colour, `gray-*` or a second radius.
- A deferred or optional prop typed as always present, or rendered without a skeleton.
- `router.cancel()`, `router.on('invalid' | 'exception', …)`, `Inertia::lazy()` — v2 API.
- Inventing a redirect target, a success message, an option list or a default the requirement
  does not state. Cite the requirement or decision, or ask.
