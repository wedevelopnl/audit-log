<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Reading;

/**
 * Reads the trail: a filtered, paginated page of entries, and the distinct
 * actors present (for filter option lists).
 */
interface RecordReader
{
    public function page(AuditQuery $query): AuditPage;

    /** @return list<AuditActor> */
    public function actors(): array;
}
