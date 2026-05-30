<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Record;

use DateTimeImmutable;
use WeDevelop\AuditLog\Event\AuditChannel;
use WeDevelop\AuditLog\Event\Changeset;
use WeDevelop\AuditLog\Event\RenderPayload;

/**
 * The immutable read shape of a stored record. No mutators anywhere: a record
 * never changes after it is written.
 */
interface AuditRecord
{
    public string $id { get; }
    public string $code { get; }
    public AuditChannel $channel { get; }
    public DateTimeImmutable $occurredAt { get; }
    public ?string $actorId { get; }
    public ?string $actorLabel { get; }
    public ?string $subjectClass { get; }
    public ?string $subjectId { get; }
    public ?string $subjectLabel { get; }
    public ?string $ipAddress { get; }
    public ?Changeset $changes { get; }
    /** @var array<string, mixed>|null */
    public ?array $data { get; }
    public RenderPayload $render { get; }
}
