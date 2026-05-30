# wedevelopnl/audit-log

Immutable, self-contained audit trail for Symfony and Doctrine.

An audit record answers — durably and credibly — **who** did **what**, to **what**,
**when**, from **where**, and **what changed**, and keeps answering it regardless of
what later happens to the rest of the system. Records are immutable snapshots of a
past fact: they survive deletion of the actor, the subject, and the producing code.

> **Status:** this package currently ships the framework-free core (value objects,
> the `AuditEvent` contract, the `Recorder` and its ports, the read contracts and
> DTOs). Doctrine persistence and the Symfony bundle land in subsequent releases.

## Requirements

- PHP 8.5+
- [`psr/clock`](https://packagist.org/packages/psr/clock), [`psr/log`](https://packagist.org/packages/psr/log)

## Installation

```bash
composer require wedevelopnl/audit-log
```

## Design

Three responsibilities are deliberately separated:

| Responsibility | Type | Concern |
| --- | --- | --- |
| Describe an act | `AuditEvent` | Pure data + phrasing. No clock, identity, or services. |
| Capture the moment | `Recorder` | Resolves time, actor, origin, subject label; freezes everything. |
| The durable read shape | `AuditRecord` | Immutable snapshot, interpretable without the producing code. |

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

Record it through the `Recorder` — the single write seam. It captures the ambient
strands of the moment (clock, acting principal, origin, subject label) and appends a
frozen record:

```php
$recorder->record(new UserRoleChanged($userId, 'member', 'admin'));
```

Sensitive fields are recorded as changed **without** their values:

```php
new Changeset(FieldChange::redacted('password'));
```

## License

BSD 3-Clause. See [LICENSE](LICENSE). © 2026 WeDevelop.
