<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Reading;

final readonly class AuditOrder
{
    public function __construct(
        public AuditOrderField $field,
        public SortDirection $direction,
    ) {
    }
}
