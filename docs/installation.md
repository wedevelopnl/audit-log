# Installation

```bash
composer require wedevelopnl/audit-log
```

There is no Flex recipe; register the bundle manually in `config/bundles.php`:

```php
return [
    // ...
    WeDevelop\AuditLog\AuditLogBundle::class => ['all' => true],
];
```

The bundle auto-registers its Doctrine DBAL types and the `AuditRecordEntity`
mapping. Run a migration to create the `audit_record` table, then (recommended)
grant the application role `INSERT`/`SELECT` only — `REVOKE UPDATE, DELETE` — to
enforce append-only at the database layer.

## Requirements

The default port implementations autowire framework services, so the consuming
application must have these subsystems enabled (the standard Symfony full-stack
setup already does):

- the **security** component — `SecurityActorResolver` injects `TokenStorageInterface`;
- the **translator** (`framework.translator`) — `TranslatorAuditRenderer` injects `TranslatorInterface`.

If either is disabled, container compilation fails with an autowiring error. Re-alias
the affected port (`ActorResolver` / `AuditRenderer`) to your own implementation to
drop the dependency.

## What you provide

- A `SubjectLabeller` implementation (the default returns no label). Alias the
  port to your service:
  `WeDevelop\AuditLog\Recording\SubjectLabeller: '@App\Audit\MySubjectLabeller'`.
- Optionally override `ActorResolver`/`OriginResolver` for richer actor labels or
  finer channel detection.
- One `AuditEvent` (extend `AbstractAuditEvent`) per auditable action, and a
  listener that calls `Recorder::record()`.

## Flushing

`Recorder::record()` persists the record without flushing; it commits with the
application's next `EntityManager::flush()`, atomically with the audited change.
Call `record()` before that flush. Places where no flush follows lose the record:

- `kernel.terminate` / `console.terminate` listeners;
- Doctrine `postFlush` listeners;
- anything after an `EntityManager::clear()` or reset.

The bundle logs an error (`{count} audit record(s) were recorded but never flushed
and are lost.`, with the action codes in the context) on `kernel.terminate` and
`console.terminate` when records are still waiting to be flushed. In a Messenger
handler, record before the handler's flush; worker message boundaries are not
watched. See [ADR-0010](adr/0010-records-commit-with-the-callers-flush.md).

## Configuration

```yaml
audit_log:
    translation_domain: audit   # default
```
