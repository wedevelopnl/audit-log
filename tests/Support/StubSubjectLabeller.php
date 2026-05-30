<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Support;

use Override;
use WeDevelop\AuditLog\Event\Subject;
use WeDevelop\AuditLog\Recording\SubjectLabeller;

final readonly class StubSubjectLabeller implements SubjectLabeller
{
    public function __construct(private ?string $label)
    {
    }

    #[Override]
    public function label(?Subject $subject): ?string
    {
        return $this->label;
    }
}
