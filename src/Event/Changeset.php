<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Event;

final readonly class Changeset
{
    /** @var list<FieldChange> */
    public array $fields;

    public function __construct(FieldChange ...$fields)
    {
        $this->fields = array_values($fields);
    }
}
