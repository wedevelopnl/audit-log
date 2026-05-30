<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Fixtures\Event;

use Override;
use stdClass;
use WeDevelop\AuditLog\Event\AbstractAuditEvent;
use WeDevelop\AuditLog\Event\Changeset;
use WeDevelop\AuditLog\Event\FieldChange;
use WeDevelop\AuditLog\Event\RenderLine;
use WeDevelop\AuditLog\Event\Subject;

final readonly class UserRoleChangedEvent extends AbstractAuditEvent
{
    public function __construct(
        private string $userId,
        private string $from,
        private string $to,
    ) {
    }

    #[Override]
    public function code(): string
    {
        return 'user.role_changed';
    }

    #[Override]
    public function subject(): Subject
    {
        return new Subject(stdClass::class, $this->userId);
    }

    #[Override]
    public function changes(): Changeset
    {
        return new Changeset(FieldChange::of('role', $this->from, $this->to));
    }

    /** @return array<string, scalar> */
    #[Override]
    protected function parameters(): array
    {
        return ['from' => $this->from, 'to' => $this->to];
    }

    /** @return iterable<RenderLine> */
    #[Override]
    protected function additionalInfo(): iterable
    {
        yield new RenderLine('user.role_changed.note', ['actor' => 'system']);
    }
}
