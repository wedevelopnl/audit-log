# 3. Soft actor and soft subject

- Status: Accepted
- Date: 2026-05-30

## Context

Attribution (P4) must survive deletion of the acting user and of the subject (P2).
A live foreign key or `UserInterface` reference dies with the row it points at.

## Decision

The actor MUST be stored as a frozen snapshot — a stable `id` plus a display
`label` — never a live reference or FK. The subject MUST be stored as `class` +
`identifier` + a frozen `label`. A consumer MAY add a live relation to their own
record entity, but the library MUST NOT require one.

## Consequences

The trail stays readable after the actor or subject is deleted; labels
intentionally go stale (they show who/what something was at the moment); no join
dependency for reading.
