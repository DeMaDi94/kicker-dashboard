#!/usr/bin/env bash
# PreToolUse (Bash): stop the two ways an agent can make a gate lie.
#
#   1. `phpstan --generate-baseline` / editing the baseline — hides real type errors instead
#      of fixing them. This repo has no baseline file and should not acquire one.
#   2. `npx`/`bare` invocations of the gate binaries — they resolve a different, unpinned
#      version and drop the flags the package scripts carry, so they can pass while CI fails.
#
# Requires: jq.
set -uo pipefail

command=$(jq -r '.tool_input.command // empty' 2>/dev/null)

[ -z "$command" ] && exit 0

deny() {
    jq -Rs --arg reason "$1" \
        '{hookSpecificOutput:{hookEventName:"PreToolUse",permissionDecision:"deny",permissionDecisionReason:$reason}}' \
        </dev/null
    exit 0
}

# Anchor to command position: start of line, after a separator, or after whitespace (which is
# what an `XDEBUG_MODE=off …` prefix leaves in front of the binary). An unanchored match also
# fires on prose *about* the flag inside a heredoc — that is how this file's own documentation
# first tripped it. Backtick-quoted prose no longer matches; `run phpstan --generate-baseline`
# written as flowing text still would, which is the acceptable side of the trade.
if printf '%s\n' "$command" | grep -Eq '(^|[;&|(]|[[:space:]])(\./)?(vendor/bin/)?phpstan[[:space:]][^|;&]*--(generate-baseline|allow-empty-baseline)'; then
    deny "Never generate a PHPStan baseline. This repo has none, and adding one converts a fixable type error into a permanent exception. Fix the error; as a last resort use @phpstan-ignore-next-line with a comment saying why."
fi

# A real invocation on one line, not prose about one inside a heredoc.
if printf '%s\n' "$command" | grep -Eq '(^|[;&|(]|[[:space:]])npx([[:space:]]+-{1,2}[^[:space:]]+)*[[:space:]]+(vitest|eslint|prettier|tsc|vp|vite)([[:space:]]|$)'; then
    deny "Run the gates through the package scripts, not npx: \`npm run check\` (format + lint + types), \`npm run types:check\`, \`npm run test\`, \`npm run test:e2e\`. npx resolves an unpinned version and drops the flags the scripts carry, so it can pass while CI fails. Note this project has no vitest/eslint/prettier of its own — vite-plus (\`vp\`) provides all three; see docs/STACK.md."
fi

exit 0
