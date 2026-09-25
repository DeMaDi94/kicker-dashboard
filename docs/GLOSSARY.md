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
| ACC | Zugriff — who reads and who writes; no namespace of its own: routes and `App\Domain\Users\Permission` | — | — |
| PLY | Mitspieler | `Players` | `pages/players` |
| SEA | Saisons | `Seasons` | `features/seasons` |
| MD | Spieltage | `Matchdays` | `features/matchdays`, `pages/matchdays` |
| PEN | Strafen | `Penalties` (domain only) | — |
| STD | Gesamttabelle | `Standings` (domain only) | — |
| STAT | Statistiken | `Statistics` | `features/statistics`, `pages/statistics` |
| VIS | Besucher | `Visits` | `features/visits`, `pages/visits` |

## Core entities

| Domain term | Code | Notes |
| --- | --- | --- |
| | | What it is, and the confusable neighbour it is *not*. |
| Mitspieler (player) | `Player` | A participant of the league, with the alias from the kicker Manager (PLY-01). Not a `User`: a player has no account (ACC-04). |
| Alias | `alias` | The player's name in the kicker Manager. Not the `name` the league calls them by. |
| Saison (season) | `Season` | One Bundesliga season of the league: a name such as „2025/26“ and the penalty scale (SEA-01). |
| Gelöschte Saison | soft-deleted `Season` (`trashed()`) | Gone from every view and statistic, its points kept, restorable by an admin (SEA-06). Not a permanently removed row. |
| Spieltag (matchday) | `matchday` (1–34) | One Bundesliga matchday of a season (SEA-04). A number, not a date. |
| Punkte (points) | `Score` / `points` | A player's points on one matchday from the kicker Manager (MD-01). Not the place. |
| Spieltagswertung (matchday ranking) | `readMatchdayRanking`, `KickerRow` | The block of the copied kicker league page with each name's points on one matchday (MD-05). Not the „Saisonwertung“, kicker's season total. |
| Abgeschlossener Spieltag | complete matchday (`MatchdayPlaces::isComplete`) | Every player of the season has points (MD-02). |
| Platz (place) | `place` | Rank on a matchday or in the overall table, counted densely (MD-03, D5). |
| Strafe (penalty) | `penalty`, in cents | What a place pays per complete matchday (PEN-01). |
| Startbetrag / Schrittweite | `penaltyStartCents` / `penaltyStepCents` — `PenaltyScale` | The lowest score's penalty and the amount each higher score pays less (PEN-01). |
| Zwischenabrechnung (interim settlement) | `settlementMatchday` | The last matchday of the „Hinrunde“; the penalty box is settled after it (PEN-04). Nullable: no settlement set yet. |
| Hinrunde / Rückrunde | first half / second half — `firstHalfPenaltyCents` / `secondHalfPenaltyCents` | Penalties up to and including the settlement matchday / after it (PEN-04). Not the Bundesliga's fixed halves. |
| Spieltagssieg (matchday win) | `wins`, `CompletedMatchday::winners()` | First place on a matchday; a tie counts for each (STAT-07). |
| Rote Laterne | `lanterns`, `CompletedMatchday::lanterns()` | Last place on a matchday; a tie counts for each (STAT-07). Kept in German in the UI. |
| Formkurve (form) | `form`, `FormGrade` | The day's places of the last five complete matchdays, graded good / middle / bad (STAT-07). |
| Ewige Bilanz (all-time balance) | `CareerStats` | A player's figures over all seasons (STAT-08). |
| Direktvergleich (head-to-head) | `HeadToHead` | Two players of one season, matchday by matchday (STAT-09). |
| Liga-Rekorde (league records) | `LeagueRecords`, `LeagueRecord`, `RecordHolder` | STAT-10. |
| Strafenkasse (penalty box) | `PenaltyBox` | What a season's penalties add up to, and who paid what (STAT-12). |
| Gesamttabelle (overall table) | `Standings` | The season's ranking by points over the complete matchdays (STD-01). |
| Aufruf (visit) | `Visit` | One counted request of a public page by a guest (VIS-01). Not a visitor. |
| Besucher (visitor) | `visitor` — the day's mark | Who called, told apart within one day only (VIS-02, VIS-05). Not a `User` and not a `Player`. |
| Öffentliche Seite (public page) | `PublicPage` | Saisonansicht, Mitspieler, Direktvergleich, Liga-Rekorde (VIS-01). |
| Zeitraum (period) | `VisitPeriod` | The last 7, 30, 90 or 365 days the visit statistics show, today included (VIS-04). |
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
| `PublicPage` | `SeasonView` | `season` | `Season view` — de „Saisonansicht“ |
| `PublicPage` | `Player` | `player` | `Players` — de „Mitspieler“ |
| `PublicPage` | `HeadToHead` | `compare` | `Head-to-head` — de „Direktvergleich“ |
| `PublicPage` | `Records` | `records` | `League records` — de „Liga-Rekorde“ |
