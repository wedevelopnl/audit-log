<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Infrastructure\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use WeDevelop\AuditLog\Recording\NewAuditRecord;
use WeDevelop\AuditLog\Recording\RecordStore;

/**
 * Persists a record via Doctrine. Append-only: persist without flush — the
 * record commits with the surrounding unit of work, atomically with the audited
 * change. (ORM 3 flush() is global, so the store must never call it.).
 */
final readonly class DoctrineRecordStore implements RecordStore
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Override]
    public function add(NewAuditRecord $record): void
    {
        $this->entityManager->persist(AuditRecordEntity::fromNew($record));
    }
}
