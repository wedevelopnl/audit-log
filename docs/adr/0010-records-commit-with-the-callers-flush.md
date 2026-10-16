# 10. Records commit with the caller's flush

- Status: Accepted
- Date: 2026-10-16

## Context

An audit record must commit atomically with the change it describes: a record
without its change is a false fact, and a change without its record is an
unaudited act. ORM 3 `flush()` is global to the entity manager, so a store that
flushed would also commit whatever unrelated work the caller had pending.

## Decision

`DoctrineRecordStore` MUST persist and MUST NOT flush. A record commits with the
caller's next flush, in the same transaction as the audited change. Callers MUST
record before that flush; a record made after the last flush of a request or
command (a `kernel.terminate` listener, a `postFlush` hook, after an
`EntityManager::clear()`) is never written.

Because `Recorder::record()` cannot signal that loss, `UnflushedRecordWarner`
MUST run on `kernel.terminate` and `console.terminate` and log an error for every
record still scheduled for insertion. A closed entity manager is exempt: its flush
failed and rolled the records back together with their change.

## Consequences

Atomic audit with no extra transaction management; the flush contract is the
caller's responsibility and is documented on `Recorder` and in the installation
guide; a missed flush surfaces in the error log instead of disappearing. Records
made inside a Messenger handler commit with the handler's flush; the warner does
not watch worker message boundaries.
