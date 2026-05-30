# 9. Package as a Symfony bundle

- Status: Accepted
- Date: 2026-05-30
- Supersedes: ADR-0006

## Context

ADR 0006 placed framework code in `Bridge/{Symfony,Doctrine}`, implying an
optional library-with-bridges. Every target consumer is Symfony 8 + Doctrine +
Postgres + FrankenPHP worker mode, and a non-Symfony consumer is not conceivable.
A library-with-bridges leaves recurring per-project wiring (service definitions,
Doctrine type + entity-mapping registration) to every consumer.

## Decision

Ship as a single Symfony bundle (composer `type: symfony-bundle`; hard Symfony +
Doctrine dependencies).

- The framework-free core principle of ADR 0006 is RETAINED: `Event`,
  `Recording`, `Record`, `Reading` MUST NOT depend on Symfony, Doctrine, or
  framework code (PSR contracts only), keeping a future standalone-core
  extraction cheap.
- Framework code lives in `Infrastructure/{Doctrine,Symfony}` (adapters), NOT
  `Bridge/`. The two adapter sets MUST be independent of each other.
- The composition root is `DependencyInjection/{AuditLogExtension,Configuration}`
  + `AuditLogBundle`; only it may depend on Core + both adapter sets. It pre-wires
  every port and prepends the Doctrine DBAL types and entity mapping.
- Deptrac enforces: core framework-free; adapter independence; bundle-composes-both.
- No Symfony Flex recipe: registration is one manual `config/bundles.php` edit.

## Consequences

Consistent, near-zero-config wiring across all projects (the bundle's payoff); the
package is permanently Symfony-coupled at the dependency level; the framework-free
core keeps a later standalone-core extraction cheap. Replaces ADR 0006's `Bridge/`
structure and its single `core ↛ Bridge` rule.
