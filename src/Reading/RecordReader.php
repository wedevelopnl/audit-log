<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Reading;

/**
 * actors() returns the distinct actors present in the trail, for building
 * filter option lists: one entry per actor, under the label of their most recent
 * record (the id when it carried none). It is unpaginated and reads the whole
 * trail, so cache it if the trail is large.
 */
interface RecordReader
{
    public function page(AuditQuery $query): AuditPage;

    /** @return list<AuditActor> */
    public function actors(): array;
}
