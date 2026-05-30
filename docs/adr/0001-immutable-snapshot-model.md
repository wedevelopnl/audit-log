# 1. Immutable snapshot model

- Status: Accepted
- Date: 2026-05-30

## Context

An audit trail must answer — durably and credibly — who did what, to what, when,
from where, and what changed, and keep answering it regardless of what later
happens to the rest of the system. A model that treats an entry as a live object
serialized on write and reconstructed (re-rendered through current code) on read
fails this: it cannot carry write-time snapshots, it re-renders history through
today's code, and it breaks when the producing class is renamed or deleted.

## Decision

An audit record MUST be an immutable, self-contained snapshot of a past fact. The
architecture derives from six governing properties:

- **P1 Immutable** — a record never changes after it is written; mutability MUST be
  structurally absent, not merely discouraged.
- **P2 Self-contained** — a record MUST stay fully interpretable after its actor,
  subject, and producing code are gone.
- **P3 Faithful to the moment** — a record states what was true at the instant of
  the act, not what the system would compute now.
- **P4 Attributable** — every record ties an actor to an action upon a subject.
- **P5 Intelligible** — a record can be shown to a human, in their language,
  without the originating code.
- **P6 Queryable** — the trail is searchable by actor, subject, action, and time.

A record MUST NOT be produced by rehydrating its producing class. ADRs 0002–0008
derive directly from these properties.

## Consequences

Durability across deletion and refactor; mutation is forbidden; ambient knowledge
of the moment MUST be captured at write time; denormalization (frozen labels) is
intended, not a leak.
