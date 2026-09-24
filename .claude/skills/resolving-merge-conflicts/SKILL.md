---
name: resolving-merge-conflicts
description: "Use when you need to resolve an in-progress git merge/rebase conflict."
license: MIT
metadata:
  source: mattpocock/skills@1.2.2 skills/engineering/resolving-merge-conflicts
  adapted: "Names this repo's checks; rules for the status ledger and the translation catalogues; tickets replaced by the requirement ids and decisions."
---

1. **See the current state** of the merge/rebase. Check git history, and the conflicting files.

2. **Find the primary sources** for each conflict. Understand deeply why each change was made, and what the original intent was. Read the commit messages, check the PRs, and follow the requirement ids and decision ids (`docs/REQUIREMENTS.md`, `docs/DECISIONS.md`) the changed lines and tests cite.

3. **Resolve each hunk.** Preserve both intents where possible. Where incompatible, pick the one matching the merge's stated goal and note the trade-off. Do **not** invent new behaviour. Always resolve; never `--abort`.

   - **`docs/spec/status.txt`, `lang/*.json`**: keep both sides' entries. Where both sides changed the **same requirement id or the same translation key** to different values, that is a conflict of intent, not of text — ask the user which one holds.
   - **`docs/REQUIREMENTS.md`**: the contract. Resolve nothing in it on your own; show the user both sides.
   - **`docs/spec/COVERAGE.md`** is generated and not hand-edited — `git checkout --ours` it and let `php artisan spec:coverage` rewrite it.

4. Run the project's **automated checks** and fix anything the merge broke:

   ```bash
   composer test                    # Pint, PHPStan, spec gate, Pest
   npm run check && npm run test    # format + lint + types, frontend tests
   php artisan spec:coverage        # the traceability gate
   ```

5. **Finish the merge/rebase.** Stage everything and commit. If rebasing, continue the rebase process until all commits are rebased.
