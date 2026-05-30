<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Recording;

/**
 * The acting principal, frozen at the moment: a stable id plus a display label.
 */
final readonly class Actor
{
    public function __construct(
        public string $id,
        public string $label,
    ) {
    }
}
