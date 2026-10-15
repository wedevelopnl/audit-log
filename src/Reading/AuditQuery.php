<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Reading;

use DateTimeImmutable;
use InvalidArgumentException;
use WeDevelop\AuditLog\Event\AuditChannel;

use function sprintf;

/**
 * All filters are optional and combine with AND. Without an `order`, the
 * trail reads newest first.
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
        public ?AuditOrder $order = null,
    ) {
        if ($page < 1) {
            throw new InvalidArgumentException(sprintf('Page must be 1 or greater, got %d.', $page));
        }

        if ($perPage < 1) {
            throw new InvalidArgumentException(sprintf('Per page must be 1 or greater, got %d.', $perPage));
        }
    }
}
