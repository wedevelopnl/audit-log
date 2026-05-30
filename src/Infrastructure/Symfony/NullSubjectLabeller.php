<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Infrastructure\Symfony;

use Override;
use WeDevelop\AuditLog\Event\Subject;
use WeDevelop\AuditLog\Recording\SubjectLabeller;

/**
 * Default labeller: no label. There is no generic way to label an arbitrary
 * subject, so the bundle ships a no-op and the consumer overrides this port.
 */
final readonly class NullSubjectLabeller implements SubjectLabeller
{
    #[Override]
    public function label(?Subject $subject): ?string
    {
        return null;
    }
}
