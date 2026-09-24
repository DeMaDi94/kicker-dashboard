#!/usr/bin/env bash
# PostToolUse (Edit|Write): format the file that was just written, so the diff Claude
# reviews is already what CI expects. Async — never blocks a turn.
#
# Requires: jq.
set -uo pipefail

cd "${CLAUDE_PROJECT_DIR:-$(git rev-parse --show-toplevel 2>/dev/null || pwd)}" || exit 0

file=$(jq -r '.tool_response.filePath // .tool_input.file_path // empty' 2>/dev/null)

[ -z "$file" ] && exit 0
[ -f "$file" ] || exit 0

# docs/ is never formatted. The requirement catalogue is laid out by hand, and code comments cite
# REQUIREMENTS.md by its wording.
case "$file" in
    docs/*|*/docs/*) exit 0 ;;
esac

case "$file" in
    *.php)
        ./vendor/bin/pint --quiet "$file" >/dev/null 2>&1
        ;;
    *.ts|*.tsx|*.css)
        # shadcn primitives are vendored generated code; vite.config.ts excludes them.
        case "$file" in
            *resources/js/components/ui/*) exit 0 ;;
        esac
        ./node_modules/.bin/vp fmt "$file" >/dev/null 2>&1
        ;;
esac

exit 0
