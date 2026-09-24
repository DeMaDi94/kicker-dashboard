# GLOSSARY.md Format

`docs/GLOSSARY.md` is a fixed set of tables. Add rows; never add a section, a free-text definition list, or a second glossary file. Both columns of a row are binding — the domain term as the catalogue spells it, the identifier as the code spells it.

## Area → code namespace

```md
| Prefix | Area | PHP namespace | Frontend folder |
| --- | --- | --- | --- |
| INV | Invoicing | `Billing` | `features/billing` |
```

One row per area prefix in `docs/REQUIREMENTS.md`. The prefix and the area come from the catalogue; only the namespace and folder are named here. A row without a catalogue area behind it is an invented area — ask instead.

## Core entities

```md
| Domain term | Code | Notes |
| --- | --- | --- |
| Rechnung (invoice) | `Invoice` | A request for payment sent after delivery. Not a `Quote`, which asks for nothing. |
```

- **Domain term** — as the catalogue writes it, with the English gloss in parentheses when the catalogue is not English.
- **Code** — the one identifier every file uses. A variant (a trait, a scope like `trashed()`) goes here too, not in a new row per spelling.
- **Notes** — what it IS in one or two sentences, then the **confusable neighbour it is not**. The neighbour is the point of the column: it is what stops the next session reaching for the wrong term.

## Terms of art that stay untranslated

```md
| Term | Why it stays | Where |
| --- | --- | --- |
| Skonto | A German payment-discount term with no faithful English equivalent. | `Billing\Skonto`, invoice detail |
```

Only for terms whose translation loses the meaning — legal or industry terms. Spelled exactly as the spec spells it.

## Status and enum values

```md
| Enum | Case | Stored value | Label key |
| --- | --- | --- | --- |
| `InvoiceStatus` | `Paid` | `paid` | `Paid` — de „Bezahlt“ |
```

One row per case. The case is English, the stored value is the wire format (not free to prettify once data exists), the label is a translation key with each locale's wording as the requirement quotes it.

## Rules

- **Be opinionated.** When multiple words exist for the same concept, pick the best one; name the others as the neighbour in Notes if they are genuinely confusable.
- **Keep definitions tight.** One or two sentences max. Define what it IS, not what it does.
- **Only include terms specific to this product.** General programming concepts (timeouts, error types, utility patterns) don't belong even if the project uses them extensively. Before adding a term, ask: is this a concept unique to this domain, or a general programming concept? Only the former belongs.
