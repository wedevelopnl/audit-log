<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Fixtures\Event;

use Override;
use WeDevelop\AuditLog\Event\AbstractAuditEvent;

/**
 * An event that overrides nothing but code(): exercises every safe default on
 * the base.
 */
final readonly class MinimalAuditEvent extends AbstractAuditEvent
{
    #[Override]
    public function code(): string
    {
        return 'minimal.event';
    }
}
