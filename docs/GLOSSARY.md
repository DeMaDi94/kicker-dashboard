# Glossary — the domain's words, and what we call them in code

`docs/REQUIREMENTS.md` is written in the product's own vocabulary. The code is English. Without a
fixed mapping every session invents its own translation, and one domain term becomes `item`,
`entry`, `lineItem` and `position` in four different files.

**The rule:** identifiers are English. Both columns below are binding. If you need a term that is
not here, add it here in the same change.

## Area → code namespace

One row per area prefix in the catalogue. `{Area}` in `.claude/rules/architecture.md` means the
namespace column — do not invent an area name.

| Prefix | Area | PHP namespace | Frontend folder |
| --- | --- | --- | --- |
| — | User management — a blueprint area (B13–B16), not in the catalogue | `Users` | `features/users` |
| | _the product's areas: filled by the `start-project` skill_ | | |

## Core entities

| Domain term | Code | Notes |
| --- | --- | --- |
| | | What it is, and the confusable neighbour it is *not*. |
| Benutzer (user) | `User` | An account that can sign in. Also the name of the plain role — the enum case `Role::User`. |
| Rolle (role) | `Role` | What a user is, exactly one per user (B13). Not a permission: code never checks a role. |
| Berechtigung (permission) | `Permission` | What a role allows, e.g. `users.view`. The only thing code checks. |
| Gelöschter Benutzer | soft-deleted `User` (`trashed()`) | Deleted, but restorable (B15). Not a permanently removed row. |
| Einladung (invitation) | `UserInvitation` | The mail a new user sets their password through (B14). Not the password-reset mail. |

## Terms of art that stay untranslated

Where translating a term loses its meaning (a legal or industry term with no faithful English
equivalent), it stays in the original, spelled exactly as the spec spells it.

| Term | Why it stays | Where |
| --- | --- | --- |

## Status and enum values

Enum **cases** are English; their stored value is the wire format; their label is a translation key.

| Enum | Case | Stored value | Label key |
| --- | --- | --- | --- |
| `Role` | `Admin` | `admin` | `Admin` — de „Administrator“ |
| `Role` | `User` | `user` | `User` — de „Benutzer“ |
