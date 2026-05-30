# 4. Stable action codes, not class-strings

- Status: Accepted
- Date: 2026-05-30

## Context

A record must remain interpretable after the producing code is renamed or deleted
(P2). Identifying the action by its PHP class-string couples history to symbol
names.

## Decision

A record's action MUST be identified by a stable string code (e.g.
`user.deleted`). Codes MUST be unique and append-only — once shipped, a code is
never repurposed for a different meaning. Class-strings MUST NOT be the stored
identifier.

## Consequences

Refactors and deletions do not corrupt history; requires code-naming discipline;
an optional registry MAY map codes to translation/filter metadata.
