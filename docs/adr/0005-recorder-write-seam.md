# 5. The Recorder is the single write seam

- Status: Accepted
- Date: 2026-05-30

## Context

Building a record needs write-time knowledge (clock, acting principal, subject
label) that a pure event value object cannot and should not reach.

## Decision

Record construction MUST occur in a `Recorder` service — the single writer —
through injectable ports: a clock, `ActorResolver`, `SubjectLabeller`,
`OriginResolver`, `IdentityGenerator`, and `RecordStore`. The `AuditEvent` MUST
stay pure (no clock, identity, or services). There MUST be exactly one write path.

## Consequences

One place to extend the write path (e.g. a future integrity chain); event objects
are trivially testable; more interfaces than a single method, which is justified
because each port is an independently-varying strand of the moment.
