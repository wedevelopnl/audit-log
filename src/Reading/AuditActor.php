<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Reading;

/**
 * Distinct actor as seen in the trail, for building filter option lists.
 */
final readonly class AuditActor
{
    public function __construct(
        public string $id,
        public string $label,
    ) {
    }
}
