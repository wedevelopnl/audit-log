# 6. Framework-free core, framework in bridges

- Status: Accepted
- Date: 2026-05-30

## Context

The library is the default audit module across (Symfony) projects, but its
semantics are framework-independent.

## Decision

`Event`, `Recording`, `Record`, and `Reading` MUST NOT depend on Symfony,
Doctrine, or `Bridge` code; only PSR contracts are permitted in the core. All
framework wiring MUST live in `Bridge/{Symfony,Doctrine}`. A deptrac rule MUST
enforce core ↛ `Bridge`.

## Consequences

The core is portable and fast to test; persistence and wiring are swappable; one
architectural boundary to maintain.
