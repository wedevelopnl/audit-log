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
 * reports any record still waiting in the unit of work.
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
        // A closed manager means the flush itself failed: the records rolled back
        // together with the change they audit, which is not a loss to report.
        if (!$this->entityManager->isOpen()) {
            return;
        }

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
