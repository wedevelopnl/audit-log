<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Infrastructure\Doctrine;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use WeDevelop\AuditLog\Event\AuditChannel;
use WeDevelop\AuditLog\Event\RenderLine;
use WeDevelop\AuditLog\Event\RenderPayload;
use WeDevelop\AuditLog\Infrastructure\Doctrine\DoctrineRecordStore;
use WeDevelop\AuditLog\Infrastructure\Doctrine\UnflushedRecordWarner;
use WeDevelop\AuditLog\Recording\NewAuditRecord;
use WeDevelop\AuditLog\Tests\Support\Doctrine\EntityManagerFactory;
use WeDevelop\AuditLog\Tests\Support\SpyLogger;

#[CoversClass(UnflushedRecordWarner::class)]
final class UnflushedRecordWarnerTest extends TestCase
{
    private EntityManagerInterface $em;
    private SpyLogger $logger;
    private UnflushedRecordWarner $warner;

    #[Override]
    protected function setUp(): void
    {
        $this->em = EntityManagerFactory::createWithSchema();
        $this->logger = new SpyLogger();
        $this->warner = new UnflushedRecordWarner($this->em, $this->logger);
    }

    public function testReportsEveryRecordStillWaitingForAFlush(): void
    {
        $this->add('0190a8e0-0001-7000-8000-000000000001', 'user.deleted');
        $this->add('0190a8e0-0002-7000-8000-000000000002', 'user.created');

        ($this->warner)();

        self::assertSame([[
            'level' => LogLevel::ERROR,
            'message' => '{count} audit record(s) were recorded but never flushed and are lost.',
            'context' => ['count' => 2, 'codes' => ['user.deleted', 'user.created']],
        ]], $this->logger->records);
    }

    public function testStaysSilentOnceTheRecordsAreFlushed(): void
    {
        $this->add('0190a8e0-0001-7000-8000-000000000001', 'user.deleted');
        $this->em->flush();

        ($this->warner)();

        self::assertSame([], $this->logger->records);
    }

    public function testStaysSilentWhenTheFlushFailedAndClosedTheManager(): void
    {
        $this->add('0190a8e0-0001-7000-8000-000000000001', 'user.deleted');
        $this->em->close();

        ($this->warner)();

        self::assertSame([], $this->logger->records);
    }

    private function add(string $id, string $code): void
    {
        new DoctrineRecordStore($this->em)->add(new NewAuditRecord(
            id: $id,
            code: $code,
            channel: AuditChannel::Console,
            occurredAt: new DateTimeImmutable('2026-05-01T00:00:00+00:00'),
            actorId: null,
            actorLabel: null,
            subjectClass: null,
            subjectId: null,
            subjectLabel: null,
            ipAddress: null,
            changes: null,
            data: null,
            render: new RenderPayload(new RenderLine($code)),
        ));
    }
}
