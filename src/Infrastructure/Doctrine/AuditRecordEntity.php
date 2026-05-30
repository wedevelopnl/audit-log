<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Infrastructure\Doctrine;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use WeDevelop\AuditLog\Event\AuditChannel;
use WeDevelop\AuditLog\Event\Changeset;
use WeDevelop\AuditLog\Event\RenderPayload;
use WeDevelop\AuditLog\Infrastructure\Doctrine\Type\ChangesetType;
use WeDevelop\AuditLog\Infrastructure\Doctrine\Type\RenderPayloadType;
use WeDevelop\AuditLog\Record\AuditRecord;
use WeDevelop\AuditLog\Recording\NewAuditRecord;

/**
 * The default stored record. Append-only: a private constructor, a single named
 * factory, no setters. Doctrine hydrates via reflection (asymmetric visibility
 * is transparent to it).
 */
#[ORM\Entity]
#[ORM\Table(name: 'audit_record')]
#[ORM\Index(fields: ['code'])]
#[ORM\Index(fields: ['occurredAt'])]
#[ORM\Index(fields: ['actorId'])]
#[ORM\Index(fields: ['subjectClass', 'subjectId'])]
class AuditRecordEntity implements AuditRecord
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID)]
    public private(set) string $id;

    #[ORM\Column(type: Types::STRING)]
    public private(set) string $code;

    #[ORM\Column(type: Types::STRING, length: 16, enumType: AuditChannel::class)]
    public private(set) AuditChannel $channel;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    public private(set) DateTimeImmutable $occurredAt;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    public private(set) ?string $actorId;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    public private(set) ?string $actorLabel;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    public private(set) ?string $subjectClass;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    public private(set) ?string $subjectId;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    public private(set) ?string $subjectLabel;

    #[ORM\Column(type: Types::STRING, length: 45, nullable: true)]
    public private(set) ?string $ipAddress;

    #[ORM\Column(type: ChangesetType::NAME, nullable: true)]
    public private(set) ?Changeset $changes;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    public private(set) ?array $data;

    #[ORM\Column(type: RenderPayloadType::NAME)]
    public private(set) RenderPayload $render;

    private function __construct()
    {
    }

    public static function fromNew(NewAuditRecord $record): self
    {
        $entity = new self();
        $entity->id = $record->id;
        $entity->code = $record->code;
        $entity->channel = $record->channel;
        $entity->occurredAt = $record->occurredAt;
        $entity->actorId = $record->actorId;
        $entity->actorLabel = $record->actorLabel;
        $entity->subjectClass = $record->subjectClass;
        $entity->subjectId = $record->subjectId;
        $entity->subjectLabel = $record->subjectLabel;
        $entity->ipAddress = $record->ipAddress;
        $entity->changes = $record->changes;
        $entity->data = $record->data;
        $entity->render = $record->render;

        return $entity;
    }
}
