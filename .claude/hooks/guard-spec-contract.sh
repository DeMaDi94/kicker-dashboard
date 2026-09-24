#!/usr/bin/env bash
# PreToolUse (Edit|Write): the requirement catalogue is the contract, not working material.
#
# docs/REQUIREMENTS.md is what the whole app is measured against; editing it to match the code
# inverts that. Writing it is still legitimate — the catalogue is drafted with the user when a
# project starts (the start-project skill), and it grows as the product does — so this does not
# deny the write. It asks: every change to the contract gets a human's explicit yes.
#
# Requires: jq.
set -uo pipefail

payload=$(cat)
file=$(printf '%s' "$payload" | jq -r '.tool_input.file_path // empty' 2>/dev/null)

[ -z "$file" ] && exit 0

# Normalise to a repo-relative path.
root="${CLAUDE_PROJECT_DIR:-$(git rev-parse --show-toplevel 2>/dev/null || pwd)}"
relative="${file#"$root"/}"

ask() {
    jq -Rs --arg reason "$1" \
        '{hookSpecificOutput:{hookEventName:"PreToolUse",permissionDecision:"ask",permissionDecisionReason:$reason}}' \
        </dev/null
    exit 0
}

case "$relative" in
    docs/REQUIREMENTS.md)
        ask "docs/REQUIREMENTS.md is the contract this app is measured against. Change it only because the user asked for this change to the requirements — never to make the code or a test agree with it. To record that something was built differently, set the requirement to \`changed\` in docs/spec/status.txt with a reason instead."
        ;;
esac

exit 0
