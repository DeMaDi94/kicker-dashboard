---
paths:
  - 'resources/js/**'
  - 'resources/css/**'
---

# Frontend architecture

Inertia 3 + React 19 + TypeScript + Tailwind 4 + shadcn/ui. Not a separate SPA: there is no client
router and no data-fetching library. The server hands a page its props; the page renders them.
Do not reach for React Query, `useQuery` or `fetch`-in-an-effect for page data.

## The layers

```
resources/js/
├─ pages/{area}/{screen}.tsx   Inertia page — thin: takes props, composes
├─ features/{area}/            that area's own components and hooks — the actual work
├─ components/core/            cross-area behavioural primitives (see below)
├─ components/ui/              shadcn — generated, do not hand-edit
├─ layouts/shell/              the application shell: rail, header, tab bar, user chip
├─ lib/                        pure functions, incl. i18n and pinned mirrors of domain rules
└─ types/                      shared prop and entity shapes
```

**Boundary:** `features/{area}` may not import from another `features/{area}`. If two areas need
the same thing, it goes to `components/core/` or `lib/`. This is the one structural rule; it is
what stops two screens growing a shared tangle.

## The core primitives come before the screens

shadcn/ui gives buttons, dialogs and inputs. It does not give the behaviours nearly every screen
needs. Reuse the primitive — never re-solve it inline:

| Primitive | Behaviour |
| --- | --- |
| `useConfirm()` / `usePrompt()` / `useAlert()` | Promise-returning styled modals. Native `confirm`/`prompt`/`alert` are never used. |
| `toast()` | Three kinds (`info ℹ`, `ok ✓`, `err ⚠`), 4.5 s, closable, must never throw. |
| `<Panel>` / `<PanelHeader>` / `<PanelBody>` | The one surface: a card held apart by a hairline, no shadow. |
| `<PageTitle>` | The heading every screen opens with, and the slot for its primary action. |
| `<GroupRule>` / `<AddButton>` / `<DeleteButton>` | Group labels, the dashed "+ add" control, the quiet row delete. |
| `<SortableList>` | Handle-only (⠿) pointer drag, reorder confined to one list or group. |
| `useAutosave()` | 300 ms debounce, **scoped to the state passed in**, flushes on leave, never overlaps. |
| `useUndoRedo()` | Snapshot-based, 450 ms debounce, depth 80, shortcuts inert in textareas. |
| `useListQuery()` / `<ListPager>` | Search, filters and page live in the URL; typing debounced 300 ms. |
| `useLivePoll()` | Partial reload of the named props every 6 s while the tab is visible. |
| `<NumberInput>` | Locale-aware parse and format, negatives clamp to 0 unless allowed. |
| `<AutoGrowTextarea>` | A multi-line field that grows with its content. |
| `<ViewErrorBoundary>` | A view that throws surfaces as a toast; the shell keeps working. |
| `activatableProps()` | Anything acting as a button/link/checkbox without being one is focusable and keyboard-operable. |

The timings above are the blueprint's defaults (docs/DECISIONS.md). A project that needs other
values changes them in the primitive, with a requirement or decision id — never per call site.

The live gallery of all of them is `/_primitives` (local only). Add a new primitive there too.

## Conventions

- **Files** kebab-case. **Components** `PascalCase`, hooks `useThing`, other identifiers
  `camelCase`. Props types are `{Screen}Props`, colocated with the page.
- **Every user-facing string goes through `t('…')`** — labels, headings, buttons, placeholders,
  `aria-label`, `title`, toasts. See `.claude/rules/i18n.md`.
- **No type assertions** (`as Type`). Write code the compiler can infer; `as const` is fine.
  `noUncheckedIndexedAccess` is on, so index access is `T | undefined` — handle it, don't assert
  it away.
- **Never `any`.** `unknown` plus a narrowing check.
- **Design tokens only.** Colours, the radius, the focus ring and the two breakpoints come from
  `resources/css/theme.css`: `bg-brand-*`, `text-brand-*`, `border-brand-line`, `rounded-brand`,
  `shadow-focus`, the `compact:` (820 px) and `phone:` (480 px) variants. Never a raw hex or a
  `820px` media query in a component. shadcn's semantic tokens (`bg-primary`, `text-muted-foreground`)
  are pointed at the same palette in `app.css`, so either reads the same value.
- **One radius, one focus ring, no decorative shadow.** Every radius step shadcn uses resolves to
  `rounded-brand`. Surfaces are separated by the `border-brand-line` hairline, not a shadow. Do not
  reintroduce a second radius.
- **The type idioms are utilities.** `brand-label` is the small upper-case Silkscreen label;
  `brand-figure` is the mono figure column. Do not re-specify face, size and tracking by hand.
- **Mirrors of domain rules must be pinned.** PHP is authoritative for every stored value. A TS
  mirror exists only where interaction needs instant feedback, and it is held to the same fixtures
  as the PHP. An unpinned second implementation of a rule is how the two sides start disagreeing.
- **No new dependency without approval**, and check first: `sonner`, `@dnd-kit/*`, `date-fns` and
  the shadcn components in `components/ui` are already installed. See `docs/STACK.md`.

## Version traps — Inertia 3, Wayfinder, Tailwind 4

Knowledge of older versions produces code that looks right and fails. Adapted from the Boost
guidelines the packages ship (MIT); the full reference is the `inertia-react-development` skill.

- **Inertia 3:** `Inertia::lazy()` is gone — `Inertia::optional()`. Axios is gone — the built-in
  client. `router.cancel()` → `router.cancelAll()`. Events renamed: `invalid` → `httpException`,
  `exception` → `networkError`. No `future` config. `optional()`/`defer()`/`merge()` work on
  dot-notation paths inside nested props. A deferred prop renders a skeleton while it loads.
- **Wayfinder, never a hardcoded URL.** An invokable controller is a default import from
  `@/actions/App/Http/{Area}/{Action}/{Action}Controller` (`<Form {...StoreUserController.form()}>`,
  `StoreUserController.url()`); named routes come from `@/routes/{name}` (`edit(user)`). The Vite
  plugin regenerates both — do not hand-edit `resources/js/actions` or `resources/js/routes`.
- **Tailwind 4:** CSS-first — tokens live in `@theme` in `resources/css/`; there is no
  `tailwind.config.js`, no `@tailwind` directive, no `corePlugins`. Removed utilities:
  `*-opacity-*` → the `/` modifier (`bg-brand-ink/50`), `flex-shrink-*` → `shrink-*`,
  `flex-grow-*` → `grow-*`, `overflow-ellipsis` → `text-ellipsis`, `decoration-slice|clone` →
  `box-decoration-slice|clone`. Space siblings with `gap-*`, not margins.
