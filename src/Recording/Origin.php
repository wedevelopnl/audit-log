<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Recording;

use WeDevelop\AuditLog\Event\AuditChannel;

/**
 * Where the act entered the system: the channel and the network address.
 */
final readonly class Origin
{
    public function __construct(
        public AuditChannel $channel,
        public ?string $ipAddress,
    ) {
    }
}
