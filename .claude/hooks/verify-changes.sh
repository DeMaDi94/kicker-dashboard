#!/usr/bin/env bash
# Stop: run the static gates over the files this session changed and BLOCK if any fails.
#
# Scope is the working tree (staged + unstaged + untracked), not the branch diff: that is
# exactly the work an agent turn produces, it stays bounded, and it never blocks on a
# teammate's already-committed code. Once committed, CI is the gate.
#
# Playwright is deliberately NOT run here — it boots a server and takes minutes. The block
# message names the command instead.
#
# Requires: jq. Without it every hook fails open (exit 0), so nothing breaks and nothing
# is checked either. `brew install jq`.
set -uo pipefail

payload=$(cat)

# --- 0. loop guards -------------------------------------------------------------------
[ "$(printf '%s' "$payload" | jq -r '.stop_hook_active // false')" = "true" ] && exit 0

session=$(printf '%s' "$payload" | jq -r '.session_id // "unknown"')
counter="${TMPDIR:-/tmp}/claude-verify-${session}.count"
blocks=$(cat "$counter" 2>/dev/null || echo 0)
# Belt and braces: never block the same session more than 3 times.
[ "$blocks" -ge 3 ] 2>/dev/null && exit 0

cd "${CLAUDE_PROJECT_DIR:-$(git rev-parse --show-toplevel 2>/dev/null || pwd)}" || exit 0

# --- 1. what changed ------------------------------------------------------------------
changed=$(git status --porcelain --untracked-files=all 2>/dev/null | sed 's/^...//' | sed 's/.* -> //')
[ -z "$changed" ] && exit 0

existing() { while IFS= read -r f; do [ -n "$f" ] && [ -f "$f" ] && printf '%s\n' "$f"; done; }

php_files=$(printf '%s\n' "$changed" | grep -E '\.php$'  | grep -vE '^(vendor|storage)/' | existing || true)
ts_files=$(printf  '%s\n' "$changed" | grep -E '\.tsx?$' | grep -vE '^node_modules/'     | existing || true)
spec_touched=$(printf '%s\n' "$changed" | grep -E '^docs/REQUIREMENTS\.md$|^docs/spec/status\.txt$' || true)
lang_touched=$(printf '%s\n' "$changed" | grep -E '^lang/' || true)

[ -z "$php_files" ] && [ -z "$ts_files" ] && [ -z "$spec_touched" ] && [ -z "$lang_touched" ] && exit 0

failures=""
add_failure() { failures="${failures}
### ${1}
${2}
"; }

# --- 2. the gates ---------------------------------------------------------------------
if [ -n "$php_files" ]; then
    if ! out=$(./vendor/bin/pint --test $php_files 2>&1); then
        add_failure "Pint found unformatted PHP (run \`./vendor/bin/pint\`)" "$out"
    fi
    # Only the paths phpstan.neon actually covers. Passing a file outside them overrides the
    # config's scope, and `tests/` is excluded there on purpose — Pest's fluent `arch()` API
    # has no PHPStan stubs, so analysing it reports errors that are not errors.
    #
    # The level is not passed either: phpstan.neon pins it, and a flag here would quietly
    # become the truth instead.
    analysable=$(printf '%s\n' "$php_files" \
        | grep -E '^(app|config|database|routes)/|^bootstrap/app\.php$' || true)

    if [ -n "$analysable" ]; then
        if ! out=$(XDEBUG_MODE=off ./vendor/bin/phpstan analyse --no-progress --error-format=raw \
                    $analysable 2>&1); then
            add_failure "PHPStan failed" "$out"
        fi
    fi
fi

if [ -n "$ts_files" ]; then
    if ! out=$(./node_modules/.bin/vp lint $ts_files 2>&1); then
        add_failure "vp lint failed" "$out"
    fi
    # tsc has no per-file mode that still sees the project's types — run the project.
    if ! out=$(./node_modules/.bin/tsc --noEmit 2>&1); then
        add_failure "tsc --noEmit failed" "$out"
    fi
fi

# --- 3. requirement traceability ------------------------------------------------------
# The gate no other tool provides: nothing may claim `done` without a test citing it, and
# no test may cite a requirement the catalogue does not declare.
if ! out=$(php artisan spec:coverage --no-write 2>&1); then
    add_failure "Requirement traceability failed (docs/spec/status.txt vs. the tests that cite ids)" \
                "$(printf '%s\n' "$out" | grep -vE '^\s*[|+]' | grep -v '^\s*$')"
fi

# --- 4. translations ------------------------------------------------------------------
# Every key a component or a PHP file passes to t() / __() must exist in every locale file,
# or the UI quietly shows the raw key in that language. One fast test file, no database.
if [ -n "$php_files" ] || [ -n "$ts_files" ] || [ -n "$lang_touched" ]; then
    if [ -f tests/Architecture/TranslationsTest.php ]; then
        if ! out=$(./vendor/bin/pest tests/Architecture/TranslationsTest.php 2>&1); then
            add_failure "Missing translations (add the key to every lang/*.json — see .claude/rules/i18n.md)" \
                        "$(printf '%s\n' "$out" | grep -vE '^\s*$' | tail -40)"
        fi
    fi
fi

# --- 5. block or pass -----------------------------------------------------------------
if [ -z "$failures" ]; then
    rm -f "$counter"
    exit 0
fi

echo $((blocks + 1)) > "$counter"

reminder="Tests are not run by this hook. Run them yourself: composer test (PHP) / npm run test (frontend) / npm run test:e2e (Playwright)."

printf '%s' "$failures
$reminder" | jq -Rs '{decision:"block", reason:("Verification gate failed on the files you changed. Fix these before finishing.\n" + .)}'
exit 0
