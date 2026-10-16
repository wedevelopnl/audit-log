<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Infrastructure\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Override;
use WeDevelop\AuditLog\Reading\AuditActor;
use WeDevelop\AuditLog\Reading\AuditEntry;
use WeDevelop\AuditLog\Reading\AuditOrder;
use WeDevelop\AuditLog\Reading\AuditOrderField;
use WeDevelop\AuditLog\Reading\AuditPage;
use WeDevelop\AuditLog\Reading\AuditQuery;
use WeDevelop\AuditLog\Reading\RecordReader;
use WeDevelop\AuditLog\Reading\SortDirection;

final readonly class DoctrineRecordReader implements RecordReader
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Override]
    public function page(AuditQuery $query): AuditPage
    {
        $rows = $this->ordered($this->filtered($query)->select('r'), $query->order)
            ->setFirstResult(($query->page - 1) * $query->perPage)
            ->setMaxResults($query->perPage)
            ->getQuery()
            ->getResult();

        /** @var list<AuditRecordEntity> $rows */
        return new AuditPage(
            array_map($this->toEntry(...), $rows),
            $query->page,
            $query->perPage,
            $this->count($query),
        );
    }

    /** @return list<AuditActor> */
    #[Override]
    public function actors(): array
    {
        // Each actor's most recent record: no later record by the same actor
        // exists, with the id breaking ties within one instant.
        $later = $this->entityManager->createQueryBuilder()
            ->select('l.id')
            ->from(AuditRecordEntity::class, 'l')
            ->where('l.actorId = r.actorId')
            ->andWhere('l.occurredAt > r.occurredAt OR (l.occurredAt = r.occurredAt AND l.id > r.id)');

        $qb = $this->entityManager->createQueryBuilder();

        /** @var list<array{id: string, label: string|null}> $rows */
        $rows = $qb
            ->from(AuditRecordEntity::class, 'r')
            ->select('r.actorId AS id', 'r.actorLabel AS label')
            ->where('r.actorId IS NOT NULL')
            ->andWhere($qb->expr()->not($qb->expr()->exists($later->getDQL())))
            ->orderBy('label', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(
            static fn (array $row): AuditActor => new AuditActor($row['id'], $row['label'] ?? $row['id']),
            $rows,
        );
    }

    private function count(AuditQuery $query): int
    {
        return (int) $this->filtered($query)
            ->select('COUNT(r.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function filtered(AuditQuery $query): QueryBuilder
    {
        $qb = $this->entityManager->createQueryBuilder()->from(AuditRecordEntity::class, 'r');

        if (null !== $query->code) {
            $qb->andWhere('r.code = :code')->setParameter('code', $query->code);
        }
        if (null !== $query->actorId) {
            $qb->andWhere('r.actorId = :actorId')->setParameter('actorId', $query->actorId);
        }
        if (null !== $query->subjectClass) {
            $qb->andWhere('r.subjectClass = :subjectClass')->setParameter('subjectClass', $query->subjectClass);
        }
        if (null !== $query->subjectId) {
            $qb->andWhere('r.subjectId = :subjectId')->setParameter('subjectId', $query->subjectId);
        }
        if (null !== $query->channel) {
            $qb->andWhere('r.channel = :channel')->setParameter('channel', $query->channel->value);
        }
        if (null !== $query->from) {
            $qb->andWhere('r.occurredAt >= :from')->setParameter('from', $query->from);
        }
        if (null !== $query->to) {
            $qb->andWhere('r.occurredAt <= :to')->setParameter('to', $query->to);
        }

        return $qb;
    }

    /**
     * Applies the requested order, or newest-first without one. Every order
     * ends on the id so rows sharing a value keep one position across pages.
     */
    private function ordered(QueryBuilder $qb, ?AuditOrder $order): QueryBuilder
    {
        if (null === $order) {
            return $qb->orderBy('r.occurredAt', 'DESC')->addOrderBy('r.id', 'DESC');
        }

        $column = match ($order->field) {
            AuditOrderField::OccurredAt => 'r.occurredAt',
            AuditOrderField::Code => 'r.code',
            AuditOrderField::Channel => 'r.channel',
            AuditOrderField::Actor => 'r.actorLabel',
            AuditOrderField::Subject => 'r.subjectLabel',
        };
        $direction = SortDirection::Asc === $order->direction ? 'ASC' : 'DESC';

        return $qb->orderBy($column, $direction)->addOrderBy('r.id', $direction);
    }

    private function toEntry(AuditRecordEntity $record): AuditEntry
    {
        return new AuditEntry(
            $record->id,
            $record->code,
            $record->channel,
            $record->occurredAt,
            $record->actorId,
            $record->actorLabel,
            $record->subjectClass,
            $record->subjectId,
            $record->subjectLabel,
            $record->ipAddress,
            $record->changes,
            $record->data,
            $record->render,
        );
    }
}
