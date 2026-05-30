<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Event;

use InvalidArgumentException;

use function is_scalar;
use function sprintf;

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
        // The changeset is frozen as JSON, so values must round-trip faithfully:
        // an object silently reshapes (or loses its class) and a resource/closure
        // throws mid-flush, inside the audited transaction. Curate to a scalar.
        self::assertJsonSafe($field, 'old', $old);
        self::assertJsonSafe($field, 'new', $new);

        return new self($field, $old, $new, false);
    }

    private static function assertJsonSafe(string $field, string $side, mixed $value): void
    {
        if (null !== $value && !is_scalar($value)) {
            throw new InvalidArgumentException(sprintf('FieldChange "%s" %s value must be a scalar or null; got %s.', $field, $side, get_debug_type($value)));
        }
    }

    public static function redacted(string $field): self
    {
        return new self($field, null, null, true);
    }
}
