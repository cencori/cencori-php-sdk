# Cencori PHP SDK — justfile
# Run with: `just <command>`

composer := "composer"
phpunit := "vendor/bin/phpunit"
precommit := "pre-commit"

# ── Setup ────────────────────────────────────────────────────────────────

# Install dependencies and hooks
setup:
    {{ composer }} install
    {{ precommit }} install

# Update dependencies
update:
    {{ composer }} update

# ── Lint ─────────────────────────────────────────────────────────────────

# Check PHP syntax on all source files
lint:
    @find src -name '*.php' -exec php -l {} \; > /dev/null
    @find tests -name '*.php' -exec php -l {} \; > /dev/null
    @echo "All files pass syntax check"

# ── Test ─────────────────────────────────────────────────────────────────

# Run tests
test:
    {{ phpunit }}

# Run tests with coverage
test-cov:
    php -d zend_extension=xdebug {{ phpunit }} --coverage-html coverage/

# ── Pre-commit ───────────────────────────────────────────────────────────

# Install pre-commit hooks
hooks:
    {{ precommit }} install

# Run pre-commit on all files
hooks-run:
    {{ precommit }} run --all-files

# Update pre-commit hook versions
hooks-update:
    {{ precommit }} autoupdate

# ── Full Check ───────────────────────────────────────────────────────────

# Run all checks
check: lint test

# ── Default ──────────────────────────────────────────────────────────────

default: check
