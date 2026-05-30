<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Recording;

use DateTimeImmutable;
use WeDevelop\AuditLog\Event\AuditChannel;
use WeDevelop\AuditLog\Event\Changeset;
use WeDevelop\AuditLog\Event\RenderPayload;

/**
 * The frozen result the recorder assembles and hands to the store. Write-input
 * only; the durable read shape is Record\AuditRecord.
 */
final readonly class NewAuditRecord
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
