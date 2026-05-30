# 2. Render frozen as translation keys and parameters

- Status: Accepted
- Date: 2026-05-30

## Context

A record must remain human-readable in the viewer's language without the
originating code (P5), reflect the moment (P3), and survive class deletion (P2).
Three ways to persist meaning: hold a live object and render on read; store the
already-rendered string; store the translation key plus parameters.

## Decision

At the moment of the act, the recorder MUST freeze a render payload consisting of
a translation key and its parameters (and any additional lines) — NOT a live
object, NOT a pre-rendered string. Reading MUST translate the frozen payload.

- A live object fails P2 (needs the class) and P3 (re-renders through today's code).
- A pre-rendered string fails P5 (locale frozen at write).

## Consequences

i18n stays late-bound on read; records are independent of the producing class;
changing what a code's parameters mean is a forward-only concern (see ADR 0004).
Rehydrating the payload value from JSON does not reintroduce a class dependency —
it is structural data, not the producing class.
