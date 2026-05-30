<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Event;

/**
 * Frozen render tree: translation inputs captured at the moment of the act —
 * never a live object, never a pre-rendered string.
 */
final readonly class RenderPayload
{
    /** @param list<RenderLine> $info */
    public function __construct(
        public RenderLine $message,
        public array $info = [],
    ) {
    }
}
