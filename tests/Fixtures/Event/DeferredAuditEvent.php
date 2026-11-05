<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Fixtures\Event;

use Override;
use WeDevelop\AuditLog\Event\AbstractAuditEvent;
use WeDevelop\AuditLog\Recording\Actor;
use WeDevelop\AuditLog\Recording\Origin;
use WeDevelop\AuditLog\Recording\ProvidesActor;
use WeDevelop\AuditLog\Recording\ProvidesOrigin;

/**
 * An act recorded away from the request that caused it (a Messenger handler),
 * carrying the actor and origin captured when the work was dispatched.
 */
final readonly class DeferredAuditEvent extends AbstractAuditEvent implements ProvidesActor, ProvidesOrigin
{
    public function __construct(
        private ?Actor $actor,
        private Origin $origin,
    ) {
    }

    #[Override]
    public function code(): string
    {
        return 'import.completed';
    }

    #[Override]
    public function actor(): ?Actor
    {
        return $this->actor;
    }

    #[Override]
    public function origin(): Origin
    {
        return $this->origin;
    }
}
