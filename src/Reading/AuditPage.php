<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Reading;

use InvalidArgumentException;

use function sprintf;

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
        if ($perPage < 1) {
            throw new InvalidArgumentException(sprintf('Per page must be 1 or greater, got %d.', $perPage));
        }

        $this->pageCount = max(1, (int) ceil($this->total / $this->perPage));
    }
}
