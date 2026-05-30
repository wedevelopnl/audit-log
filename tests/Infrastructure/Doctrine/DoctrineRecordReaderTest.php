<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Infrastructure\Doctrine;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;
use WeDevelop\AuditLog\Event\AuditChannel;
use WeDevelop\AuditLog\Event\RenderLine;
use WeDevelop\AuditLog\Event\RenderPayload;
use WeDevelop\AuditLog\Infrastructure\Doctrine\DoctrineRecordReader;
use WeDevelop\AuditLog\Infrastructure\Doctrine\DoctrineRecordStore;
use WeDevelop\AuditLog\Reading\AuditActor;
use WeDevelop\AuditLog\Reading\AuditEntry;
use WeDevelop\AuditLog\Reading\AuditPage;
use WeDevelop\AuditLog\Reading\AuditQuery;
use WeDevelop\AuditLog\Recording\NewAuditRecord;
use WeDevelop\AuditLog\Tests\Support\Doctrine\EntityManagerFactory;

#[CoversClass(DoctrineRecordReader::class)]
#[CoversClass(AuditEntry::class)]
#[CoversClass(AuditActor::class)]
#[CoversClass(AuditPage::class)]
#[CoversClass(AuditQuery::class)]
final class DoctrineRecordReaderTest extends TestCase
{
    private EntityManagerInterface $em;
    private DoctrineRecordStore $store;
    private DoctrineRecordReader $reader;

    #[Override]
    protected function setUp(): void
    {
        $this->em = EntityManagerFactory::createWithSchema();
        $this->store = new DoctrineRecordStore($this->em);
        $this->reader = new DoctrineRecordReader($this->em);
    }

    public function testFiltersByCodeAndPaginatesNewestFirst(): void
    {
        $newest = '0190a8e0-0003-7000-8000-000000000003';
        $this->persist('0190a8e0-0001-7000-8000-000000000001', 'user.deleted', 'actor-1', 'Jan', '2026-05-01T00:00:00+00:00');
        $this->persist('0190a8e0-0002-7000-8000-000000000002', 'user.created', 'actor-1', 'Jan', '2026-05-02T00:00:00+00:00');
        $this->persist($newest, 'user.deleted', 'actor-2', 'Ana', '2026-05-03T00:00:00+00:00');

        $page = $this->reader->page(new AuditQuery(code: 'user.deleted', perPage: 1));

        self::assertSame(2, $page->total);
        self::assertSame(2, $page->pageCount);
        self::assertCount(1, $page->entries);
        self::assertSame($newest, $page->entries[0]->id);
        // The reader returns detached DTOs, never the EM-managed entity, so the
        // result carries no persistence handle out of the read boundary.
        self::assertFalse($this->em->contains($page->entries[0]));
    }

    public function testPaginatesToTheSecondPageSkippingNewerEntries(): void
    {
        $oldest = '0190a8e0-0001-7000-8000-000000000001';
        $this->persist($oldest, 'user.deleted', 'actor-1', 'Jan', '2026-05-01T00:00:00+00:00');
        $this->persist('0190a8e0-0002-7000-8000-000000000002', 'user.deleted', 'actor-2', 'Ana', '2026-05-03T00:00:00+00:00');

        $page = $this->reader->page(new AuditQuery(perPage: 1, page: 2));

        self::assertSame(2, $page->total);
        self::assertCount(1, $page->entries);
        self::assertSame($oldest, $page->entries[0]->id);
    }

    public function testFiltersByDateRange(): void
    {
        $recent = '0190a8e0-0002-7000-8000-000000000002';
        $this->persist('0190a8e0-0001-7000-8000-000000000001', 'user.deleted', 'actor-1', 'Jan', '2026-05-01T00:00:00+00:00');
        $this->persist($recent, 'user.deleted', 'actor-1', 'Jan', '2026-06-01T00:00:00+00:00');

        $page = $this->reader->page(new AuditQuery(from: new DateTimeImmutable('2026-05-15T00:00:00+00:00')));

        self::assertSame(1, $page->total);
        self::assertSame($recent, $page->entries[0]->id);
    }

    public function testListsDistinctActorsSortedByLabel(): void
    {
        $this->persist('0190a8e0-0001-7000-8000-000000000001', 'user.deleted', 'actor-2', 'Ana', '2026-05-01T00:00:00+00:00');
        $this->persist('0190a8e0-0002-7000-8000-000000000002', 'user.deleted', 'actor-1', 'Jan', '2026-05-02T00:00:00+00:00');
        $this->persist('0190a8e0-0003-7000-8000-000000000003', 'user.deleted', 'actor-1', 'Jan', '2026-05-03T00:00:00+00:00');

        $actors = $this->reader->actors();

        self::assertCount(2, $actors);
        self::assertSame('Ana', $actors[0]->label);
        self::assertSame('Jan', $actors[1]->label);
    }

    public function testFiltersByActor(): void
    {
        $target = '0190a8e0-0002-7000-8000-000000000002';
        $this->persist('0190a8e0-0001-7000-8000-000000000001', 'user.deleted', 'actor-1', 'Jan', '2026-05-01T00:00:00+00:00');
        $this->persist($target, 'user.deleted', 'actor-2', 'Ana', '2026-05-02T00:00:00+00:00');

        $page = $this->reader->page(new AuditQuery(actorId: 'actor-2'));

        self::assertSame(1, $page->total);
        self::assertSame($target, $page->entries[0]->id);
    }

    public function testFiltersBySubjectClassAndIdIndependently(): void
    {
        $target = '0190a8e0-0002-7000-8000-000000000002';
        $this->persist('0190a8e0-0001-7000-8000-000000000001', 'user.deleted', 'actor-1', 'Jan', '2026-05-01T00:00:00+00:00', subjectClass: stdClass::class, subjectId: 'user-1');
        $this->persist($target, 'invoice.paid', 'actor-1', 'Jan', '2026-05-02T00:00:00+00:00', subjectClass: Exception::class, subjectId: 'invoice-9');

        // subjectClass alone selects the one record of that class...
        $byClass = $this->reader->page(new AuditQuery(subjectClass: Exception::class));
        self::assertSame(1, $byClass->total);
        self::assertSame($target, $byClass->entries[0]->id);

        // ...and subjectId is bound to subject_id, not subject_class.
        $byId = $this->reader->page(new AuditQuery(subjectId: 'invoice-9'));
        self::assertSame(1, $byId->total);
        self::assertSame($target, $byId->entries[0]->id);
    }

    public function testFiltersByChannel(): void
    {
        $target = '0190a8e0-0002-7000-8000-000000000002';
        $this->persist('0190a8e0-0001-7000-8000-000000000001', 'user.deleted', 'actor-1', 'Jan', '2026-05-01T00:00:00+00:00', channel: AuditChannel::Ui);
        $this->persist($target, 'user.deleted', 'actor-1', 'Jan', '2026-05-02T00:00:00+00:00', channel: AuditChannel::Api);

        $page = $this->reader->page(new AuditQuery(channel: AuditChannel::Api));

        self::assertSame(1, $page->total);
        self::assertSame($target, $page->entries[0]->id);
    }

    public function testFiltersByInclusiveFromToWindow(): void
    {
        $inWindow = '0190a8e0-0002-7000-8000-000000000002';
        $this->persist('0190a8e0-0001-7000-8000-000000000001', 'user.deleted', 'actor-1', 'Jan', '2026-05-01T00:00:00+00:00');
        $this->persist($inWindow, 'user.deleted', 'actor-1', 'Jan', '2026-05-10T00:00:00+00:00');
        $this->persist('0190a8e0-0003-7000-8000-000000000003', 'user.deleted', 'actor-1', 'Jan', '2026-05-20T00:00:00+00:00');

        // The window boundary equals the in-window record's timestamp, pinning
        // from as >= (inclusive lower) and to as <= (inclusive upper).
        $page = $this->reader->page(new AuditQuery(
            from: new DateTimeImmutable('2026-05-10T00:00:00+00:00'),
            to: new DateTimeImmutable('2026-05-15T00:00:00+00:00'),
        ));

        self::assertSame(1, $page->total);
        self::assertSame($inWindow, $page->entries[0]->id);
    }

    /** @param class-string $subjectClass */
    private function persist(
        string $id,
        string $code,
        string $actorId,
        string $actorLabel,
        string $at,
        AuditChannel $channel = AuditChannel::Ui,
        string $subjectClass = stdClass::class,
        string $subjectId = 'user-1',
    ): void {
        $this->store->add(new NewAuditRecord(
            id: $id,
            code: $code,
            channel: $channel,
            occurredAt: new DateTimeImmutable($at),
            actorId: $actorId,
            actorLabel: $actorLabel,
            subjectClass: $subjectClass,
            subjectId: $subjectId,
            subjectLabel: null,
            ipAddress: null,
            changes: null,
            data: null,
            render: new RenderPayload(new RenderLine($code)),
        ));
        $this->em->flush();
    }
}
