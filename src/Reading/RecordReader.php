<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Reading;

/**
 * actors() returns the distinct actors present in the trail, for building
 * filter option lists.
 */
interface RecordReader
{
    public function page(AuditQuery $query): AuditPage;

    /** @return list<AuditActor> */
    public function actors(): array;
}
