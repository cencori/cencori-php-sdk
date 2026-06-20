# Contributing to Cencori PHP SDK

Thanks for your interest in contributing! This document covers the development workflow.

## Prerequisites

- PHP 8.1+
- [Composer](https://getcomposer.org) — dependency manager
- [just](https://github.com/casey/just) — command runner (install via `brew install just` or `cargo install just`)
- [pre-commit](https://pre-commit.com) — git hook framework

## Setup

```bash
# Clone the repo and enter the directory
git clone git@github.com:cencori/cencori-php-sdk.git
cd cencori-php-sdk

# Install dependencies and pre-commit hooks
just setup
```

This runs `composer install` and sets up git hooks.

## Development Workflow

### Run all checks

```bash
just check
```

This runs: PHP syntax lint → tests.

### Individual commands

```bash
just lint   # PHP syntax check on all source files
just test   # Run tests (PHPUnit)
```

### Pre-commit hooks

Hooks are defined in `.pc.yaml` and run automatically on `git commit`. They check:

- **php-lint** — `php -l` syntax check on all `.php` files
- **trailing-whitespace** — removes trailing whitespace
- **end-of-file-fixer** — ensures files end with a newline

You can also run them manually:

```bash
just hooks-run    # Run pre-commit on all files
just hooks-update # Update hook versions
```

## Project Structure

```
src/
├── Cencori.php           # Main client
├── AIModule.php          # AI — chat, streaming, embeddings
├── ProjectsModule.php    # Project management
├── APIKeysModule.php     # API key management
├── MetricsModule.php     # Usage analytics
├── ComputeModule.php     # Coming soon stub
├── WorkflowModule.php    # Coming soon stub
├── StorageModule.php     # Coming soon stub
├── VectorsSubmodule.php  # Coming soon stub
├── Errors/               # Error class hierarchy
│   ├── CencoriError.php
│   ├── AuthenticationError.php
│   ├── RateLimitError.php
│   ├── SafetyError.php
│   ├── InsufficientCreditsError.php
│   └── ProviderError.php
└── Types/                # Type classes with fromArray() factories
    ├── Message.php
    ├── ChatResponse.php
    ├── StreamChunk.php
    ├── Project.php
    ├── APIKey.php
    ├── MetricsResponse.php
    └── ... (20+ types)
tests/           # PHPUnit tests
examples/        # Usage examples
```

## Pull Request Guidelines

1. Create a branch from `main` with a descriptive name (`feat/...`, `fix/...`, `chore/...`)
2. Make your changes, keeping them focused and minimal
3. Run `just check` and ensure everything passes
4. Commit using clear messages (we use conventional commits)
5. Push and open a PR

## Adding Dependencies

```bash
composer require <package>          # Runtime dependency
composer require --dev <package>    # Dev dependency
```

## License

By contributing, you agree that your contributions will be licensed under the MIT License.
