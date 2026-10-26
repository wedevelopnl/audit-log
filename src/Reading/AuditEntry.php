<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Reading;

use DateTimeImmutable;
use Override;
use WeDevelop\AuditLog\Event\AuditChannel;
use WeDevelop\AuditLog\Event\Changeset;
use WeDevelop\AuditLog\Event\RenderPayload;
use WeDevelop\AuditLog\Record\AuditRecord;

final readonly class AuditEntry implements AuditRecord
{
    /** @param array<string, mixed>|null $data */
    public function __construct(
        #[Override]
        public string $id,
        #[Override]
        public string $code,
        #[Override]
        public AuditChannel $channel,
        #[Override]
        public DateTimeImmutable $occurredAt,
        #[Override]
        public ?string $actorId,
        #[Override]
        public ?string $actorLabel,
        #[Override]
        public ?string $subjectClass,
        #[Override]
        public ?string $subjectId,
        #[Override]
        public ?string $subjectLabel,
        #[Override]
        public ?string $ipAddress,
        #[Override]
        public ?Changeset $changes,
        #[Override]
        public ?array $data,
        #[Override]
        public RenderPayload $render,
    ) {
    }

    /** A detached copy of any stored record, carrying no persistence handle. */
    public static function fromRecord(AuditRecord $record): self
    {
        return new self(
            id: $record->id,
            code: $record->code,
            channel: $record->channel,
            occurredAt: $record->occurredAt,
            actorId: $record->actorId,
            actorLabel: $record->actorLabel,
            subjectClass: $record->subjectClass,
            subjectId: $record->subjectId,
            subjectLabel: $record->subjectLabel,
            ipAddress: $record->ipAddress,
            changes: $record->changes,
            data: $record->data,
            render: $record->render,
        );
    }
}
