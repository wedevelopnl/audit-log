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

Redaction exists only on `FieldChange`; the `data` payload has no redaction
mechanism. `data` MUST therefore never carry secrets or values that would
require redaction. It is for non-sensitive context the domain deliberately keeps
(e.g. a deleted user's email, so the trail stays meaningful) — the domain owns
that judgement.

## Consequences

The security boundary stays with the domain; diffs render uniformly; curation
discipline is required of consumers.
