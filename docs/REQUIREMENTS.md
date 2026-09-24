# Requirements

What the application must **do**. Every requirement here is functional: a rule a user, a document
or another system depends on. How it is stored, rendered or delivered is not part of the contract
unless a requirement says so.

| | |
| --- | --- |
| Product | Vivalaraza — Tabellen und Strafen der internen kicker-Manager-Liga |
| Requirements | 17 |
| Last reviewed | 2026-09-24 (Q1–Q8 eingearbeitet) |

Decisions and open questions live in [`DECISIONS.md`](DECISIONS.md); the domain vocabulary in
[`GLOSSARY.md`](GLOSSARY.md).

---

## How to read this

**These requirements are the contract.** Where a value, a label or an ordering appears below, it
was stated by the product owner. If a rule looks arbitrary, it may be — ask before improving it.

**Quoted UI text is part of the requirement.** Labels, statuses, messages and document strings in
*„quotes“* are verbatim in the language they are given in, and become that locale's translation.

**Ids are stable.** `ABC-01`: a 2–4 letter area prefix and a two-digit number. Ids are never
renumbered and never reused; a retired requirement stays in the ledger as `wont-do` with a reason.
Quote them in branch names, commit subjects, test descriptions and code comments.

## Format

The tooling (`php artisan spec:index`, `spec:coverage`) parses this file line by line. An area is a
numbered level-2 heading with its prefix in backticks; a requirement is a bullet starting with its
bold id and an em dash; continuation lines are indented two spaces:

    ## 3. Invoices (`INV`)

    *One sentence on what the area is for.*

    ### Constants

    | Constant | Value |
    | --- | --- |
    | Payment term | 14 days |

    - **INV-01** — Invoice numbers follow `RE-YYYY-NNNN`, **gapless per calendar year**,
      zero-padded to four digits.
    - **INV-02** — A finalised invoice is immutable; a correction is a cancellation plus a new
      invoice. The cancelled number stays taken.

(The example above is indented so that the parser ignores it.)

## Table of contents

1. Zugriff (`ACC`)
2. Mitspieler (`PLY`)
3. Saisons (`SEA`)
4. Spieltage (`MD`)
5. Strafen (`PEN`)
6. Gesamttabelle (`STD`)

---

## 1. Zugriff (`ACC`)

*Wer was sehen und wer was eintragen darf.*

- **ACC-01** — Die Ergebnisse jeder Saison (Spieltage, Gesamttabelle, Strafen) sind **ohne
  Anmeldung** lesbar. Die öffentliche Seite zeigt die Gesamttabelle mit Punkte- und
  Strafensumme je Mitspieler, die Ergebnisse je Spieltag (Punkte, Platz, Strafe) und eine
  Auswahl früherer Saisons; vorausgewählt ist die zuletzt angelegte Saison.
- **ACC-02** — Punkte eintragen und ändern dürfen nur angemeldete Benutzer.
- **ACC-03** — Mitspieler und Saisons anlegen sowie die Mitspieler einer Saison festlegen dürfen
  nur Admins. Mitspieler und Saisons werden nur angelegt, nicht bearbeitet oder gelöscht.
- **ACC-04** — Ein Mitspieler braucht kein Benutzerkonto; Mitspieler und Benutzer sind getrennt.

## 2. Mitspieler (`PLY`)

*Die Teilnehmer der internen Liga im kicker Manager.*

- **PLY-01** — Ein Mitspieler hat einen Namen und seinen Alias aus dem kicker Manager.

## 3. Saisons (`SEA`)

*Eine Bundesliga-Saison der internen Liga.*

### Constants

| Constant | Value |
| --- | --- |
| Spieltage je Saison | 34 (Bundesliga) |

- **SEA-01** — Ein Admin legt eine Saison an und legt dabei ihre Bezeichnung (z. B. „2025/26“)
  sowie Startbetrag und Schrittweite der Strafenstaffel fest (PEN-01). Eine Saison hat keine
  weiteren Angaben. Startbetrag und Schrittweite sind ab dem ersten eingetragenen Spieltag
  gesperrt.
- **SEA-02** — Ein Admin legt fest, welche Mitspieler an einer Saison teilnehmen.
- **SEA-03** — Die Mitspieler einer Saison ändern sich während der Saison nicht: Die
  Mitspielerliste ist ab dem ersten eingetragenen Punkt gesperrt.
- **SEA-04** — Eine Saison hat 34 Spieltage.

## 4. Spieltage (`MD`)

*Die Punkte eines Bundesliga-Spieltags, von Hand aus dem kicker Manager übernommen.*

- **MD-01** — Ein Benutzer trägt je Spieltag für jeden Mitspieler der Saison dessen Punkte aus dem
  kicker Manager ein. Es werden immer für alle Mitspieler Punkte eingetragen. Punkte sind ganze
  Zahlen; negative Punkte sind erlaubt.
- **MD-02** — Ein Spieltag ist abgeschlossen, wenn für alle Mitspieler der Saison Punkte
  eingetragen sind. Erst dann zeigt die App Platzierungen und Strafen des Spieltags.
- **MD-03** — Die Platzierung am Spieltag berechnet die App aus den Punkten: mehr Punkte ergeben
  einen besseren Platz, gleiche Punktzahl ergibt den gleichen Platz. Plätze werden dicht gezählt:
  1, 2, 3, 3, 4.
- **MD-04** — Benutzer dürfen eingetragene Punkte ändern; Platzierung, Strafen und Gesamttabelle
  folgen der Änderung.

## 5. Strafen (`PEN`)

*Nach jedem Spieltag zahlen die schwächeren Plätze in die Kasse. Ob eine Strafe bezahlt ist,
erfasst die App nicht.*

- **PEN-01** — Die Strafe je Mitspieler und abgeschlossenem Spieltag ergibt sich aus den
  **Plätzen, nicht aus den Mitspielern**: Die niedrigste Punktzahl des Spieltags zahlt den
  Startbetrag der Saison, jede nächsthöhere Punktzahl die Schrittweite weniger. Mitspieler mit
  gleicher Punktzahl zahlen denselben Betrag. Kein Betrag fällt unter 0 €.
- **PEN-02** — Beispiel mit Startbetrag 4,50 € und Schrittweite 0,50 € (vom Product Owner
  bestätigt):

  | Punkte | Platz von unten | Strafe |
  | --- | --- | --- |
  | 40 | 1. | 4,50 € |
  | 40 | 1. | 4,50 € |
  | 55 | 2. | 4,00 € |
  | 60 | 3. | 3,50 € |
  | 60 | 3. | 3,50 € |
  | 72 | 4. | 3,00 € |
  | 80 | 5. | 2,50 € |

- **PEN-03** — Die App zeigt je Mitspieler die Summe seiner Strafen in der Saison.

## 6. Gesamttabelle (`STD`)

*Die Rangliste einer Saison, gebildet wie im kicker Manager.*

- **STD-01** — Die Gesamttabelle ordnet die Mitspieler einer Saison nach der Summe ihrer Punkte,
  die meisten Punkte zuerst. Es zählen nur abgeschlossene Spieltage (MD-02). Gleiche Punktesumme
  ergibt den gleichen Platz; innerhalb eines Platzes steht der Mitspieler mit der geringeren
  Strafensumme zuerst.
