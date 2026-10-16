<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Infrastructure\Doctrine;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use WeDevelop\AuditLog\Event\AuditChannel;
use WeDevelop\AuditLog\Event\Changeset;
use WeDevelop\AuditLog\Event\FieldChange;
use WeDevelop\AuditLog\Event\RenderLine;
use WeDevelop\AuditLog\Event\RenderPayload;
use WeDevelop\AuditLog\Infrastructure\Doctrine\DoctrineRecordReader;
use WeDevelop\AuditLog\Infrastructure\Doctrine\DoctrineRecordStore;
use WeDevelop\AuditLog\Reading\AuditActor;
use WeDevelop\AuditLog\Reading\AuditEntry;
use WeDevelop\AuditLog\Reading\AuditOrder;
use WeDevelop\AuditLog\Reading\AuditOrderField;
use WeDevelop\AuditLog\Reading\AuditPage;
use WeDevelop\AuditLog\Reading\AuditQuery;
use WeDevelop\AuditLog\Reading\SortDirection;
use WeDevelop\AuditLog\Recording\NewAuditRecord;
use WeDevelop\AuditLog\Tests\Support\Doctrine\EntityManagerFactory;

use function count;

#[CoversClass(DoctrineRecordReader::class)]
#[CoversClass(AuditEntry::class)]
#[CoversClass(AuditActor::class)]
#[CoversClass(AuditPage::class)]
#[CoversClass(AuditQuery::class)]
#[CoversClass(AuditOrder::class)]
#[CoversClass(AuditOrderField::class)]
#[CoversClass(SortDirection::class)]
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

    public function testReadsBackEveryFieldOfAStoredRecord(): void
    {
        $id = '0190a8e0-0001-7000-8000-000000000001';
        $this->store->add(new NewAuditRecord(
            id: $id,
            code: 'user.role_changed',
            channel: AuditChannel::Api,
            occurredAt: new DateTimeImmutable('2026-05-30T10:00:00+00:00'),
            actorId: 'actor-1',
            actorLabel: 'Jan',
            subjectClass: stdClass::class,
            subjectId: 'user-1',
            subjectLabel: 'ana@example.com',
            ipAddress: '203.0.113.7',
            changes: new Changeset(FieldChange::of('role', 'member', 'admin'), FieldChange::redacted('password')),
            data: ['ticket' => 42],
            render: new RenderPayload(new RenderLine('user.role_changed', ['to' => 'admin']), [new RenderLine('note')]),
        ));
        $this->em->flush();
        // Read from the database, not the identity map.
        $this->em->clear();

        $page = $this->reader->page(new AuditQuery());
        self::assertCount(1, $page->entries);
        $entry = $page->entries[0];

        self::assertSame($id, $entry->id);
        self::assertSame('user.role_changed', $entry->code);
        self::assertSame(AuditChannel::Api, $entry->channel);
        self::assertSame('2026-05-30T10:00:00+00:00', $entry->occurredAt->format(DateTimeInterface::ATOM));
        self::assertSame('actor-1', $entry->actorId);
        self::assertSame('Jan', $entry->actorLabel);
        self::assertSame(stdClass::class, $entry->subjectClass);
        self::assertSame('user-1', $entry->subjectId);
        self::assertSame('ana@example.com', $entry->subjectLabel);
        self::assertSame('203.0.113.7', $entry->ipAddress);
        self::assertEquals(new Changeset(FieldChange::of('role', 'member', 'admin'), FieldChange::redacted('password')), $entry->changes);
        self::assertSame(['ticket' => 42], $entry->data);
        self::assertEquals(new RenderPayload(new RenderLine('user.role_changed', ['to' => 'admin']), [new RenderLine('note')]), $entry->render);
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

    public function testLabelsEachActorByTheirMostRecentRecord(): void
    {
        // The older label sorts after the newer one, so picking the
        // lexicographic maximum would surface the stale name.
        $this->persist('0190a8e0-0001-7000-8000-000000000001', 'user.deleted', 'actor-1', 'Zed', '2026-05-01T00:00:00+00:00');
        $this->persist('0190a8e0-0002-7000-8000-000000000002', 'user.deleted', 'actor-1', 'Anna', '2026-05-03T00:00:00+00:00');
        // Same instant: the higher id is the later record.
        $this->persist('0190a8e0-0004-7000-8000-000000000004', 'user.deleted', 'actor-2', 'Bram', '2026-05-02T00:00:00+00:00');
        $this->persist('0190a8e0-0003-7000-8000-000000000003', 'user.deleted', 'actor-2', 'Yara', '2026-05-02T00:00:00+00:00');

        self::assertEquals(
            [new AuditActor('actor-1', 'Anna'), new AuditActor('actor-2', 'Bram')],
            $this->reader->actors(),
        );
    }

    public function testLeavesRecordsWithoutAnActorOutOfTheActorList(): void
    {
        $this->persist('0190a8e0-0001-7000-8000-000000000001', 'user.deleted', null, null, '2026-05-01T00:00:00+00:00');
        $this->persist('0190a8e0-0002-7000-8000-000000000002', 'user.deleted', 'actor-1', 'Jan', '2026-05-02T00:00:00+00:00');

        self::assertEquals([new AuditActor('actor-1', 'Jan')], $this->reader->actors());
    }

    public function testFallsBackToTheActorIdWhenNoLabelWasRecorded(): void
    {
        $this->persist('0190a8e0-0001-7000-8000-000000000001', 'user.deleted', 'actor-1', null, '2026-05-01T00:00:00+00:00');

        self::assertEquals([new AuditActor('actor-1', 'actor-1')], $this->reader->actors());
    }

    /** @return iterable<string, array{AuditQuery, list<string>}> */
    public static function filterScenarios(): iterable
    {
        // The trail from seedTrail(), newest first: C (05-03), B (05-02), A (05-01).
        yield 'by code' => [new AuditQuery(code: 'user.deleted'), ['B']];
        yield 'by actor' => [new AuditQuery(actorId: 'actor-a'), ['A']];
        yield 'by subject class' => [new AuditQuery(subjectClass: Exception::class), ['C']];
        yield 'by subject id, not subject class' => [new AuditQuery(subjectId: 'invoice-9'), ['C']];
        yield 'by channel' => [new AuditQuery(channel: AuditChannel::Api), ['A']];
        yield 'from is inclusive' => [new AuditQuery(from: new DateTimeImmutable('2026-05-02T00:00:00+00:00')), ['C', 'B']];
        yield 'to is inclusive' => [new AuditQuery(to: new DateTimeImmutable('2026-05-02T00:00:00+00:00')), ['B', 'A']];
        yield 'filters combine with AND' => [new AuditQuery(code: 'user.created', actorId: 'actor-b'), []];
        yield 'no match' => [new AuditQuery(code: 'invoice.paid'), []];
    }

    /** @param list<string> $expected */
    #[DataProvider('filterScenarios')]
    public function testFiltersTheTrail(AuditQuery $query, array $expected): void
    {
        $ids = $this->seedTrail();

        $page = $this->reader->page($query);

        self::assertSame(count($expected), $page->total);
        self::assertSame(
            array_map(static fn (string $key): string => $ids[$key], $expected),
            array_map(static fn (AuditEntry $e): string => $e->id, $page->entries),
        );
    }

    /** @return iterable<string, array{AuditOrder, list<string>}> */
    public static function orderScenarios(): iterable
    {
        // ids: A = oldest, code user.created, actor "Ana", channel api
        //      B = middle, code user.deleted, actor "Jan", channel ui
        //      C = newest, code auth.logged_out, actor "Bram", channel console
        yield 'occurredAt ascending' => [new AuditOrder(AuditOrderField::OccurredAt, SortDirection::Asc), ['A', 'B', 'C']];
        yield 'occurredAt descending' => [new AuditOrder(AuditOrderField::OccurredAt, SortDirection::Desc), ['C', 'B', 'A']];
        yield 'code ascending' => [new AuditOrder(AuditOrderField::Code, SortDirection::Asc), ['C', 'A', 'B']];
        yield 'channel descending' => [new AuditOrder(AuditOrderField::Channel, SortDirection::Desc), ['B', 'C', 'A']];
        yield 'actor ascending' => [new AuditOrder(AuditOrderField::Actor, SortDirection::Asc), ['A', 'C', 'B']];
        yield 'subject descending' => [new AuditOrder(AuditOrderField::Subject, SortDirection::Desc), ['B', 'A', 'C']];
    }

    /** @param list<string> $expected */
    #[DataProvider('orderScenarios')]
    public function testOrdersByTheRequestedField(AuditOrder $order, array $expected): void
    {
        $ids = $this->seedTrail();

        $page = $this->reader->page(new AuditQuery(order: $order));

        self::assertSame(
            array_map(static fn (string $key): string => $ids[$key], $expected),
            array_map(static fn (AuditEntry $e): string => $e->id, $page->entries),
        );
    }

    public function testDefaultsToNewestFirstWithoutAnOrder(): void
    {
        $ids = $this->seedTrail();

        $page = $this->reader->page(new AuditQuery());

        self::assertSame([$ids['C'], $ids['B'], $ids['A']], array_map(static fn (AuditEntry $e): string => $e->id, $page->entries));
    }

    /** @return iterable<string, array{SortDirection, list<string>}> */
    public static function tieBreakScenarios(): iterable
    {
        yield 'ascending' => [SortDirection::Asc, [
            '0190a8e0-0001-7000-8000-000000000001',
            '0190a8e0-0002-7000-8000-000000000002',
            '0190a8e0-0003-7000-8000-000000000003',
        ]];
        yield 'descending' => [SortDirection::Desc, [
            '0190a8e0-0003-7000-8000-000000000003',
            '0190a8e0-0002-7000-8000-000000000002',
            '0190a8e0-0001-7000-8000-000000000001',
        ]];
    }

    /** @param list<string> $expected */
    #[DataProvider('tieBreakScenarios')]
    public function testBreaksTiesByIdSoPagesNeitherRepeatNorSkipRows(SortDirection $direction, array $expected): void
    {
        // Three rows sharing one code: ordering by code alone is ambiguous.
        // SQLite returns tied rows in insertion order, so they are inserted
        // out of id order; otherwise the test passes without the tie-break.
        $this->persist('0190a8e0-0002-7000-8000-000000000002', 'user.deleted', 'actor-1', 'Jan', '2026-05-02T00:00:00+00:00');
        $this->persist('0190a8e0-0003-7000-8000-000000000003', 'user.deleted', 'actor-1', 'Jan', '2026-05-03T00:00:00+00:00');
        $this->persist('0190a8e0-0001-7000-8000-000000000001', 'user.deleted', 'actor-1', 'Jan', '2026-05-01T00:00:00+00:00');
        $order = new AuditOrder(AuditOrderField::Code, $direction);

        $seen = [];
        foreach ([1, 2, 3] as $n) {
            $seen[] = $this->reader->page(new AuditQuery(page: $n, perPage: 1, order: $order))->entries[0]->id;
        }

        self::assertSame($expected, $seen);
    }

    /** @return array{A: string, B: string, C: string} */
    private function seedTrail(): array
    {
        $ids = [
            'A' => '0190a8e0-0001-7000-8000-00000000000a',
            'B' => '0190a8e0-0002-7000-8000-00000000000b',
            'C' => '0190a8e0-0003-7000-8000-00000000000c',
        ];
        $this->persist($ids['A'], 'user.created', 'actor-a', 'Ana', '2026-05-01T00:00:00+00:00', AuditChannel::Api, subjectLabel: 'Mira');
        $this->persist($ids['B'], 'user.deleted', 'actor-b', 'Jan', '2026-05-02T00:00:00+00:00', AuditChannel::Ui, subjectLabel: 'Zoë');
        $this->persist($ids['C'], 'auth.logged_out', 'actor-c', 'Bram', '2026-05-03T00:00:00+00:00', AuditChannel::Console, Exception::class, 'invoice-9', 'Anton');

        return $ids;
    }

    /** @param class-string $subjectClass */
    private function persist(
        string $id,
        string $code,
        ?string $actorId,
        ?string $actorLabel,
        string $at,
        AuditChannel $channel = AuditChannel::Ui,
        string $subjectClass = stdClass::class,
        string $subjectId = 'user-1',
        ?string $subjectLabel = null,
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
            subjectLabel: $subjectLabel,
            ipAddress: null,
            changes: null,
            data: null,
            render: new RenderPayload(new RenderLine($code)),
        ));
        $this->em->flush();
    }
}
