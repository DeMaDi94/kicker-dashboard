# Requirements

What the application must **do**. Every requirement here is functional: a rule a user, a document
or another system depends on. How it is stored, rendered or delivered is not part of the contract
unless a requirement says so.

| | |
| --- | --- |
| Product | _not yet named — see the `start-project` skill_ |
| Requirements | 0 |
| Last reviewed | — |

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

_The areas, once there are any._

---
