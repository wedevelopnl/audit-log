<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Event;

/**
 * One field's before/after. The domain curates which fields are recorded;
 * sensitive fields are recorded as changed without their values (redacted).
 */
final readonly class FieldChange
{
    private function __construct(
        public string $field,
        public mixed $old,
        public mixed $new,
        public bool $redacted,
    ) {
    }

    public static function of(string $field, mixed $old, mixed $new): self
    {
        return new self($field, $old, $new, false);
    }

    public static function redacted(string $field): self
    {
        return new self($field, null, null, true);
    }
}
