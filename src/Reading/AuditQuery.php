<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Reading;

use DateTimeImmutable;
use WeDevelop\AuditLog\Event\AuditChannel;

/**
 * Filter and pagination criteria for reading the trail. All filters are
 * optional and combine with AND.
 */
final readonly class AuditQuery
{
    /** @param class-string|null $subjectClass */
    public function __construct(
        public ?string $code = null,
        public ?string $actorId = null,
        public ?string $subjectClass = null,
        public ?string $subjectId = null,
        public ?AuditChannel $channel = null,
        public ?DateTimeImmutable $from = null,
        public ?DateTimeImmutable $to = null,
        public int $page = 1,
        public int $perPage = 50,
    ) {
    }
}
