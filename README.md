# wedevelopnl/audit-log

Immutable, self-contained audit trail for Symfony and Doctrine.

An audit record answers — durably and credibly — **who** did **what**, to **what**,
**when**, from **where**, and **what changed**, and keeps answering it regardless of
what later happens to the rest of the system. Records are immutable snapshots of a
past fact: they survive deletion of the actor, the subject, and the producing code.

The package ships as a Symfony bundle: a framework-free core (value objects, the
`AuditEvent` contract, the `Recorder` and read ports) plus Doctrine persistence and
Symfony runtime adapters, pre-wired by the bundle. The core stays free of Symfony and
Doctrine ([ADR-0009](docs/adr/0009-symfony-bundle-packaging.md)).

## Requirements

- PHP 8.5+
- Symfony 8.0 (`framework-bundle`, `security-core`, `translation`, and the components
  pulled in transitively)
- Doctrine (`dbal` ^4, `orm` ^3.6, `doctrine-bundle` ^3.2)

## Installation

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

The bundle auto-registers its Doctrine DBAL types and the `AuditRecordEntity` mapping.
See [docs/installation.md](docs/installation.md) for the migration, the recommended
append-only database grant, and the services you must provide.

## Design

Three responsibilities are deliberately separated:

| Responsibility | Type | Concern |
| --- | --- | --- |
| Describe an act | `AuditEvent` | Pure data + phrasing. No clock, identity, or services. |
| Capture the moment | `Recorder` | Resolves time, actor, origin, subject label; freezes everything. |
| The durable read shape | `AuditRecord` | Immutable snapshot, interpretable without the producing code. |
| Query the trail | `RecordReader` | Paginated, filterable reads (`AuditQuery` → `AuditPage`) for display. |

The architecture derives from six governing properties — immutable, self-contained,
faithful to the moment, attributable, intelligible, queryable. The rationale lives in
the [Architecture Decision Records](docs/adr/).

## Usage

Describe an auditable act by implementing `AuditEvent` (or extending
`AbstractAuditEvent` for sane defaults):

```php
use WeDevelop\AuditLog\Event\AbstractAuditEvent;
use WeDevelop\AuditLog\Event\Changeset;
use WeDevelop\AuditLog\Event\FieldChange;
use WeDevelop\AuditLog\Event\Subject;

final readonly class UserRoleChanged extends AbstractAuditEvent
{
    public function __construct(
        private string $userId,
        private string $from,
        private string $to,
    ) {
    }

    public function code(): string
    {
        return 'user.role_changed';
    }

    public function subject(): Subject
    {
        return new Subject(User::class, $this->userId);
    }

    public function changes(): Changeset
    {
        return new Changeset(FieldChange::of('role', $this->from, $this->to));
    }

    protected function parameters(): array
    {
        return ['from' => $this->from, 'to' => $this->to];
    }
}
```

Record it through the `Recorder` — the single write seam, autowired by the bundle.
It captures the ambient strands of the moment (clock, acting principal, origin,
subject label) and appends a frozen record:

```php
$recorder->record(new UserRoleChanged($userId, 'member', 'admin'));
```

To attach human-readable subject labels, provide a `SubjectLabeller` (the default
returns none); read the trail back through `RecordReader`. See
[docs/installation.md](docs/installation.md) for wiring.

Sensitive fields are recorded as changed **without** their values:

```php
new Changeset(FieldChange::redacted('password'));
```

## License

BSD 3-Clause. See [LICENSE](LICENSE). © 2026 WeDevelop.
