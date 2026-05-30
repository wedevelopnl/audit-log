<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Fixtures\Event;

use Override;
use stdClass;
use WeDevelop\AuditLog\Event\AbstractAuditEvent;
use WeDevelop\AuditLog\Event\Changeset;
use WeDevelop\AuditLog\Event\FieldChange;
use WeDevelop\AuditLog\Event\Subject;

final readonly class UserPasswordChangedEvent extends AbstractAuditEvent
{
    public function __construct(private string $userId)
    {
    }

    #[Override]
    public function code(): string
    {
        return 'user.password_changed';
    }

    #[Override]
    public function subject(): Subject
    {
        return new Subject(stdClass::class, $this->userId);
    }

    #[Override]
    public function changes(): Changeset
    {
        return new Changeset(FieldChange::redacted('password'));
    }
}
