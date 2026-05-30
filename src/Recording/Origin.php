<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Recording;

use WeDevelop\AuditLog\Event\AuditChannel;

final readonly class Origin
{
    public function __construct(
        public AuditChannel $channel,
        public ?string $ipAddress,
    ) {
    }
}
