#!/usr/bin/env bash
# Verify the hooks behave. Run after editing any of them:
#
#   bash .claude/hooks/smoke-test.sh
#
# This lives in a file rather than being typed as a command because the payloads below contain
# the very strings guard-gate-commands.sh refuses — typed inline, the guard would (correctly)
# deny its own test.
set -uo pipefail

cd "$(git rev-parse --show-toplevel)" || exit 1

pass=0
fail=0

# expect <label> <expected: deny|ask|allow> <hook> <payload>
expect() {
    local label=$1 want=$2 hook=$3 payload=$4 got
    got=$(printf '%s' "$payload" | ".claude/hooks/${hook}" | jq -r '.hookSpecificOutput.permissionDecision // empty' 2>/dev/null)
    [ -z "$got" ] && got="allow"

    if [ "$got" = "$want" ]; then
        printf '  ok    %-46s %s\n' "$label" "$got"
        pass=$((pass + 1))
    else
        printf '  FAIL  %-46s want %s, got %s\n' "$label" "$want" "$got"
        fail=$((fail + 1))
    fi
}

flag="--generate-baseline"

echo "guard-gate-commands.sh"
expect "phpstan baseline"            deny  guard-gate-commands.sh "{\"tool_input\":{\"command\":\"vendor/bin/phpstan analyse ${flag}\"}}"
expect "phpstan baseline, env prefix" deny guard-gate-commands.sh "{\"tool_input\":{\"command\":\"XDEBUG_MODE=off vendor/bin/phpstan analyse ${flag}\"}}"
expect "the flag inside backticks"   allow guard-gate-commands.sh "{\"tool_input\":{\"command\":\"cat <<EOF\\n refuses \\\`phpstan ${flag}\\\` \\nEOF\"}}"
expect "npx vitest"                  deny  guard-gate-commands.sh '{"tool_input":{"command":"npx vitest run"}}'
expect "npx eslint"                  deny  guard-gate-commands.sh '{"tool_input":{"command":"npx eslint ."}}'
expect "npm run test"                allow guard-gate-commands.sh '{"tool_input":{"command":"npm run test"}}'
expect "phpstan without the flag"    allow guard-gate-commands.sh '{"tool_input":{"command":"vendor/bin/phpstan analyse app/Spec"}}'
expect "no command at all"           allow guard-gate-commands.sh '{}'

echo "guard-spec-contract.sh"
expect "REQUIREMENTS.md"             ask   guard-spec-contract.sh '{"tool_input":{"file_path":"docs/REQUIREMENTS.md"}}'
expect "the status ledger"           allow guard-spec-contract.sh '{"tool_input":{"file_path":"docs/spec/status.txt"}}'
expect "the glossary"                allow guard-spec-contract.sh '{"tool_input":{"file_path":"docs/GLOSSARY.md"}}'
expect "ordinary source"             allow guard-spec-contract.sh '{"tool_input":{"file_path":"app/Spec/StatusLedger.php"}}'

echo
printf '%d passed, %d failed\n' "$pass" "$fail"
[ "$fail" -eq 0 ] || exit 1

echo
echo "Stop gate — a real run over the current working tree:"
if out=$(printf '{"session_id":"smoke"}' | .claude/hooks/verify-changes.sh) && [ -z "$out" ]; then
    echo "  ok    all gates pass on the changed files"
else
    echo "  the gate would block this turn:"
    printf '%s' "$out" | jq -r '.reason' 2>/dev/null | sed 's/^/    /' | head -40
    exit 1
fi
