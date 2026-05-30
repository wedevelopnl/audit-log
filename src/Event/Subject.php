<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Event;

final readonly class Subject
{
    /** @param class-string $class */
    public function __construct(
        public string $class,
        public string $identifier,
    ) {
    }
}
