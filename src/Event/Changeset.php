<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Event;

/**
 * Frozen, curated field-level diff: the "what changed" of an auditable act.
 * Pure structural data — reconstructable from JSON without any producing class.
 */
final readonly class Changeset
{
    /** @var list<FieldChange> */
    public array $fields;

    public function __construct(FieldChange ...$fields)
    {
        $this->fields = array_values($fields);
    }
}
