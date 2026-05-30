<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Reading;

use DateTimeImmutable;
use WeDevelop\AuditLog\Event\AuditChannel;
use WeDevelop\AuditLog\Event\Changeset;
use WeDevelop\AuditLog\Event\RenderPayload;
use WeDevelop\AuditLog\Record\AuditRecord;

final readonly class AuditEntry implements AuditRecord
{
    /** @param array<string, mixed>|null $data */
    public function __construct(
        public string $id,
        public string $code,
        public AuditChannel $channel,
        public DateTimeImmutable $occurredAt,
        public ?string $actorId,
        public ?string $actorLabel,
        public ?string $subjectClass,
        public ?string $subjectId,
        public ?string $subjectLabel,
        public ?string $ipAddress,
        public ?Changeset $changes,
        public ?array $data,
        public RenderPayload $render,
    ) {
    }
}
