<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Event;

final readonly class RenderLine
{
    /** @param array<string, scalar> $parameters */
    public function __construct(
        public string $translationKey,
        public array $parameters = [],
    ) {
    }
}
