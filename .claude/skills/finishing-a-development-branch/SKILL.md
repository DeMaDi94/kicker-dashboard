---
name: finishing-a-development-branch
description: Use when implementation on a branch or worktree is complete and you need to decide how to integrate the work — verifies every gate and the status ledger, then offers merge, pull request or keep, and discards only on an explicit request.
license: MIT
metadata:
  source: obra/superpowers@v6.4.1 skills/finishing-a-development-branch
  adapted: "the harness gates and ledger check as Step 1; Claude Code worktrees (EnterWorktree/ExitWorktree) instead of .worktrees/; 'your human partner' → 'the user'; no commit without the user's say"
---

# Finishing a Development Branch

**Core principle:** Verify the gates → Detect environment → Present options → Execute choice → Clean up.

Integration is the user's decision. Do not commit, merge, push or delete anything they have not
chosen in this step.

## Step 1: Verify

Run on the tree you are about to integrate — a green run earlier in the session only proves the
tree it ran on:

```bash
composer test                      # Pint, PHPStan, spec gate, Pest
npm run check && npm run test      # if anything under resources/js changed
php artisan spec:coverage          # rewrites docs/spec/COVERAGE.md
npm run test:e2e                   # if a user workflow changed — boots its own server
```

Then check what no gate checks:

- Every requirement this branch touched has its line in `docs/spec/status.txt`, and `done` means
  wired into a screen, not just a helper (`in-progress`).
- Every decision the work needed is recorded in `docs/DECISIONS.md`; open ones are under „Still open“.

**If anything fails**, report it and stop — the menu comes after a green suite:

```
Not ready to integrate (<N> failures). Must fix first:

[Show failures]
```

## Step 2: Detect Environment

```bash
GIT_DIR=$(cd "$(git rev-parse --git-dir)" 2>/dev/null && pwd -P)
GIT_COMMON=$(cd "$(git rev-parse --git-common-dir)" 2>/dev/null && pwd -P)
WORKTREE_PATH=$(git rev-parse --show-toplevel)
git status --porcelain
```

| State | Menu | Cleanup |
|-------|------|---------|
| `GIT_DIR == GIT_COMMON` (normal repo) | Standard 3 options | No worktree to clean up |
| `GIT_DIR != GIT_COMMON`, named branch | Standard 3 options | See Step 6 |
| `GIT_DIR != GIT_COMMON`, detached HEAD | Reduced 2 options (no merge) | Externally managed — leave in place |

Uncommitted changes are the user's to commit — list them and ask; never commit on your own initiative.

## Step 3: Determine Base Branch

Usually `main`. If it is not already known from the conversation or the branch's upstream, ask:
"This branch split from <your best guess> — is that correct?" Merging into the wrong base is
expensive to undo.

## Step 4: Present Options

**Normal repo and named-branch worktree — exactly these 3 options:**

```
Implementation complete. What would you like to do?

1. Merge back to <base-branch> locally
2. Push and create a Pull Request
3. Keep the branch as-is (I'll handle it later)

Which option?
```

**Detached HEAD — exactly these 2 options:**

```
Implementation complete. You're on a detached HEAD (externally managed workspace).

1. Push as new branch and create a Pull Request
2. Keep as-is (I'll handle it later)

Which option?
```

Discarding the work happens only when the user explicitly asks for it (see below). Wait for their
answer.

## Step 5: Execute Choice

### Option 1: Merge Locally

```bash
MAIN_ROOT=$(git -C "$(git rev-parse --git-common-dir)/.." rev-parse --show-toplevel)
cd "$MAIN_ROOT"
git checkout <base-branch>
git pull
git merge <feature-branch>
composer test && php artisan spec:coverage   # plus npm run check && npm run test if resources/js changed
```

If the merged result fails: stop, leave the worktree and branch in place, and investigate — nothing
has been pushed, so the merge is local and recoverable. Once green: clean up (Step 6), then
`git branch -d <feature-branch>`.

### Option 2: Push and Create PR

```bash
git push -u origin <feature-branch>
gh pr create --base <base-branch>
```

The PR body names the requirement ids moved and to what status, the decisions recorded and anything
still open. Report the URL. Keep the worktree — PR feedback is fixed there (see
`receiving-code-review`).

### Option 3: Keep As-Is

Report: "Keeping branch <name>. Worktree preserved at <path>."

### If the user asks to discard the work

Only in response to an explicit request. Confirm first:

```
This will permanently delete:
- Branch <name>
- All commits: <commit-list>
- Worktree at <path>

Type 'discard' to confirm.
```

Only the typed word `discard` authorizes it. Then change to the main repo root, clean up (Step 6)
and `git branch -D <feature-branch>`.

## Step 6: Cleanup Workspace

Runs for Option 1 and confirmed discards only. Options 2 and 3 always keep the worktree.

- **Normal repo** (`GIT_DIR == GIT_COMMON`): nothing to clean up.
- **A worktree this session entered with `EnterWorktree`**: leave it with `ExitWorktree`.
- **A worktree you created by hand with `git worktree add`**: from the main repo root,
  `git worktree remove "$WORKTREE_PATH" && git worktree prune`.
- **Anything else** belongs to the host or another session — leave it in place.

If removal is refused (`contains modified or untracked files`), those files exist nowhere else.
Never `--force` on your own initiative: show `git -C "$WORKTREE_PATH" status --porcelain -uall` and
ask whether to commit them, move them to the main repo, or delete them.

## Common Rationalizations

| Excuse | Reality |
|--------|---------|
| "Tests passed earlier this session" | Run the gates on the tree you are about to integrate. |
| "The Stop hook was green" | It runs the static half only — no Pest, no `vp test`, no e2e. |
| "They obviously want it merged" | Integration is the user's decision. Present the menu and wait. |
| "'Yeah, get rid of it' counts as confirmation" | Only the typed word `discard` authorizes deletion. |
| "The PR is up, so the worktree is clutter now" | PR feedback gets fixed in that worktree. It stays until the work lands. |
| "Removal refused — `--force` is just finishing the cleanup" | The refusal means files exist only there. Show the user and ask. |
| "The merged-result failure is probably flaky" | A failing merged result stops everything. |
| "The push was rejected — force-push will fix it" | The remote moved. Investigate; force-push only on the user's explicit request. |
