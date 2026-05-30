<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Event;

/**
 * A reference to the audited entity: its class and identifier. Never its state.
 */
final readonly class Subject
{
    /** @param class-string $class */
    public function __construct(
        public string $class,
        public string $identifier,
    ) {
    }
}
