<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Reading;

/**
 * A page of audit entries plus the metadata needed to paginate.
 */
final readonly class AuditPage
{
    public int $pageCount;

    /** @param list<AuditEntry> $entries */
    public function __construct(
        public array $entries,
        public int $page,
        public int $perPage,
        public int $total,
    ) {
        $this->pageCount = max(1, (int) ceil($this->total / $this->perPage));
    }
}
