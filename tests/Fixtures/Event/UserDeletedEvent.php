<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Fixtures\Event;

use Override;
use stdClass;
use WeDevelop\AuditLog\Event\AbstractAuditEvent;
use WeDevelop\AuditLog\Event\ProvidesSubjectLabel;
use WeDevelop\AuditLog\Event\Subject;

final readonly class UserDeletedEvent extends AbstractAuditEvent implements ProvidesSubjectLabel
{
    public function __construct(
        private string $userId,
        private string $email,
    ) {
    }

    #[Override]
    public function code(): string
    {
        return 'user.deleted';
    }

    #[Override]
    public function subject(): Subject
    {
        return new Subject(stdClass::class, $this->userId);
    }

    /** @return array{email: string} */
    #[Override]
    public function data(): array
    {
        return ['email' => $this->email];
    }

    #[Override]
    public function subjectLabel(): string
    {
        return $this->email;
    }
}
