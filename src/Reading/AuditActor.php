<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Reading;

final readonly class AuditActor
{
    public function __construct(
        public string $id,
        public string $label,
    ) {
    }
}
