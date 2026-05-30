# 7. Worker-mode ambient resolution via stateless, call-time ports

- Status: Accepted
- Date: 2026-05-30

## Context

Target deployments run FrankenPHP worker mode: the kernel and services are
long-lived singletons reused across many requests.

## Decision

The recorder, resolvers, store, and reader MUST be stateless and hold no mutable
shared or static state. Resolvers MUST read ambient request state (principal,
request, IP) live at call time and MUST NOT capture it at construction. Worker
safety MUST be enforced in CI (Igor) once the Symfony bridge exists.

## Consequences

No cross-request state bleed; momentary fidelity (P3) and worker-safety become the
same constraint; resolver ports are parameterless `resolve()` calls by design.
