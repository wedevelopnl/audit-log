# 6. Framework-free core, framework in bridges

- Status: Superseded by ADR-0009
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

The `Event` layer also serves as the shared kernel. Vocabulary referenced across
layers lives here because `Event` is the only layer that `Record`, `Recording`,
and `Reading` may all depend on. `AuditChannel` is the worked example: it is
resolved at record time from `Origin` (`Recording`), yet it also appears on the
`Record` and `Reading` types, so any other placement would force an illegal
upward dependency. New cross-cutting vocabulary belongs in `Event` only when more
than one layer genuinely references it — not by default.
