# 8. Curated Changeset; no automatic entity snapshot

- Status: Accepted
- Date: 2026-05-30

## Context

"What changed" is part of the purpose, but a reflection-based dump of the entity
rakes PII and secrets into an append-only, long-retained trail.

## Decision

Field-level changes MUST be expressed as a curated `Changeset` of `FieldChange`.
Sensitive fields MUST be recorded as changed without their values (redacted). The
library MUST NOT provide an automatic entity snapshotter. The free-form `data`
payload MUST be curated by the domain, not a dump.

## Consequences

The security boundary stays with the domain; diffs render uniformly; curation
discipline is required of consumers.
