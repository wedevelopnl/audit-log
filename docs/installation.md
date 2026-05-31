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

## What you provide

- A `SubjectLabeller` implementation (the default returns no label). Alias the
  port to your service:
  `WeDevelop\AuditLog\Recording\SubjectLabeller: '@App\Audit\MySubjectLabeller'`.
- Optionally override `ActorResolver`/`OriginResolver` for richer actor labels or
  finer channel detection.
- One `AuditEvent` (extend `AbstractAuditEvent`) per auditable action, and a
  listener that calls `Recorder::record()`.

## Configuration

```yaml
audit_log:
    translation_domain: audit   # default
```
