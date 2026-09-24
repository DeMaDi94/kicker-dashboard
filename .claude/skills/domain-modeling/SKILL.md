---
name: domain-modeling
description: Build and sharpen a project's domain model. Use when the user wants to pin down domain terminology or a ubiquitous language, record an architectural decision, or when another skill needs to maintain the domain model.
license: MIT
metadata:
  source: mattpocock/skills@1.2.2 skills/engineering/domain-modeling
  adapted: "Terms go to the docs/GLOSSARY.md tables, decisions to a D-row or a Still-open row in docs/DECISIONS.md instead of a context glossary and ADR files; REQUIREMENTS.md is only ever offered wording."
---

# Domain Modeling

Actively build and sharpen the project's domain model as you design. This is the *active* discipline — challenging terms, inventing edge-case scenarios, and writing the glossary and decisions down the moment they crystallise. (Merely *reading* `docs/GLOSSARY.md` for vocabulary is not this skill — that's a one-line habit any skill can do. This skill is for when you're changing the model, not just consuming it.)

## File structure

The model lives in three files, each with its own job:

```
docs/
├── REQUIREMENTS.md   the contract: what the product must do — never edited by this skill
├── GLOSSARY.md       domain term → identifier, area prefix → namespace
└── DECISIONS.md      how a requirement is met (D-rows), and what is still undecided
```

All three exist from the start. The table shapes are in [GLOSSARY-FORMAT.md](./GLOSSARY-FORMAT.md) — write rows in exactly those shapes, never a new section or format.

## During the session

### Challenge against the glossary

When the user uses a term that conflicts with the existing language in `docs/GLOSSARY.md`, call it out immediately. "Your glossary defines 'cancellation' as X, but you seem to mean Y — which is it?"

### Sharpen fuzzy language

When the user uses vague or overloaded terms, propose a precise canonical term. "You're saying 'account' — do you mean the Customer or the User? Those are different things."

### Discuss concrete scenarios

When domain relationships are being discussed, stress-test them with specific scenarios. Invent scenarios that probe edge cases and force the user to be precise about the boundaries between concepts.

### Cross-reference with code and the catalogue

When the user states how something works, check whether the code and `docs/REQUIREMENTS.md` agree. If you find a contradiction, surface it: "Your code cancels entire Orders, but you just said partial cancellation is possible — which is right?"

When the fix belongs in the catalogue, **offer the wording** — the requirement id, the current text, your proposed text — and let the user make the edit. `docs/REQUIREMENTS.md` is the contract; never edit it to fit the code or the conversation.

### Update the glossary inline

When a term is resolved, add or correct its row in `docs/GLOSSARY.md` right there. Don't batch these up — capture them as they happen. Use the table shapes in [GLOSSARY-FORMAT.md](./GLOSSARY-FORMAT.md).

The glossary should be totally devoid of implementation details. It is a mapping from the domain's words to the code's names and nothing else — not a spec, not a scratch pad, not a place for decisions.

**Areas are not yours to name.** The area prefix → namespace table follows the catalogue's own areas. If a term seems to need an area the table does not have, stop and ask — the area comes from `docs/REQUIREMENTS.md` or not at all.

### Record decisions sparingly

A decision earns a row in `docs/DECISIONS.md` only when all three are true:

1. **Hard to reverse** — the cost of changing your mind later is meaningful
2. **Surprising without context** — a future reader will wonder "why did they do it this way?"
3. **The result of a real trade-off** — there were genuine alternatives and you picked one for specific reasons

If any of the three is missing, skip it — the code and the requirement already say enough.

When all three hold and the user has **decided**, add a row to the Project table: the next free `D` id (scan the table for the highest and increment), `Id | Decision | Why`, one bold sentence of decision plus detail, and the reason in the Why column — including the rejected alternative when the rejection is non-obvious. If the decision replaces a `B*` or earlier `D*` row, say which and move the old row to „Superseded“ with `Replaced by` pointing at the new id.

When the user has **not** decided, it is not yours to settle: add a row to „Still open“ (`Id | Question | Blocks`), continuing that table's own id sequence, naming the requirement ids or work it blocks — and stop at that boundary. When a „Still open“ question is later answered, its row leaves that table and the answer becomes a D-row.
