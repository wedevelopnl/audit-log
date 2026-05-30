<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Event;

final readonly class RenderPayload
{
    /** @param list<RenderLine> $info */
    public function __construct(
        public RenderLine $message,
        public array $info = [],
    ) {
    }
}
