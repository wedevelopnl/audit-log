<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Infrastructure\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

use function count;

/**
 * DoctrineRecordStore only persists; the caller's flush commits the record. A
 * record made after the last flush (a terminate listener, a postFlush hook)
 * would vanish without a trace, so this runs at kernel/console terminate and
 * reports any record still waiting in the unit of work. A failed flush closes
 * and clears the manager, so records it rolled back are not reported.
 */
final readonly class UnflushedRecordWarner
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(): void
    {
        $codes = [];
        foreach ($this->entityManager->getUnitOfWork()->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof AuditRecordEntity) {
                $codes[] = $entity->code;
            }
        }

        if ([] === $codes) {
            return;
        }

        $this->logger->error('{count} audit record(s) were recorded but never flushed and are lost.', [
            'count' => count($codes),
            'codes' => $codes,
        ]);
    }
}
