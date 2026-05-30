# Contributing

Thanks for contributing to `wedevelopnl/audit-log`.

## Getting started

```bash
composer install
```

Requires PHP 8.5+ with Xdebug (for coverage).

## Quality gates

Every change must pass the full pipeline before it is merged — this is what CI runs:

```bash
composer cs-check      # coding standards (PHP CS Fixer)
composer phpstan       # static analysis (level max + 100% type coverage)
composer rector-check  # ensures #[\Override] attributes are present
composer deptrac       # architecture boundaries
composer test          # PHPUnit
```

Convenience: `composer cs-fix` applies coding-standard fixes.

## Conventions

- **Test-driven.** Write the failing test first, then the implementation. Behavioral
  tests only — pure value objects and enums are covered through the callpaths that use
  them, not with tautological assertions.
- **Architecture boundaries** are enforced by Deptrac: `Event` depends on nothing;
  `Record` and `Recording` may depend on `Event`; `Reading` may depend on `Event` and
  `Record`. The core must never depend on framework bridges.
- **Stable action codes.** A record's action is a stable, append-only string code
  (e.g. `user.deleted`) — never a class-string. Once shipped, a code is never
  repurposed.
- **Fundamental decisions** are captured as [ADRs](docs/adr/); add one when you make a
  decision that governs the design.
- **Commits** follow [Conventional Commits](https://www.conventionalcommits.org/) and
  must be signed.

## Pull requests

Open a PR against `main`. CI (the `QA` workflow) must be green before review.
