<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Infrastructure\Doctrine;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;
use WeDevelop\AuditLog\Event\AuditChannel;
use WeDevelop\AuditLog\Event\Changeset;
use WeDevelop\AuditLog\Event\FieldChange;
use WeDevelop\AuditLog\Event\RenderLine;
use WeDevelop\AuditLog\Event\RenderPayload;
use WeDevelop\AuditLog\Infrastructure\Doctrine\AuditRecordEntity;
use WeDevelop\AuditLog\Infrastructure\Doctrine\DoctrineRecordStore;
use WeDevelop\AuditLog\Infrastructure\Doctrine\Type\ChangesetType;
use WeDevelop\AuditLog\Infrastructure\Doctrine\Type\RenderPayloadType;
use WeDevelop\AuditLog\Recording\NewAuditRecord;
use WeDevelop\AuditLog\Tests\Support\Doctrine\EntityManagerFactory;

#[CoversClass(DoctrineRecordStore::class)]
#[CoversClass(AuditRecordEntity::class)]
#[CoversClass(ChangesetType::class)]
#[CoversClass(RenderPayloadType::class)]
final class DoctrineRecordStoreTest extends TestCase
{
    public function testPersistsAndRoundTripsEveryFieldIncludingRedactedChanges(): void
    {
        $em = EntityManagerFactory::createWithSchema();
        $store = new DoctrineRecordStore($em);
        $id = '0190a8e0-0001-7000-8000-000000000001';

        $store->add(new NewAuditRecord(
            id: $id,
            code: 'user.password_changed',
            channel: AuditChannel::Api,
            occurredAt: new DateTimeImmutable('2026-05-30T10:00:00+00:00'),
            actorId: 'actor-1',
            actorLabel: 'Jan',
            subjectClass: stdClass::class,
            subjectId: 'user-1',
            subjectLabel: 'jan@example.com',
            ipAddress: '203.0.113.7',
            changes: new Changeset(FieldChange::redacted('password'), FieldChange::of('email', 'a@x', 'b@x')),
            data: ['ticket' => 42],
            render: new RenderPayload(new RenderLine('user.password_changed', ['by' => 'self']), [new RenderLine('note')]),
        ));
        $em->flush();
        $em->clear();

        $entity = $em->find(AuditRecordEntity::class, $id);
        self::assertInstanceOf(AuditRecordEntity::class, $entity);
        self::assertSame('user.password_changed', $entity->code);
        self::assertSame(AuditChannel::Api, $entity->channel);
        self::assertSame('203.0.113.7', $entity->ipAddress);
        self::assertSame(['ticket' => 42], $entity->data);

        self::assertNotNull($entity->changes);
        self::assertTrue($entity->changes->fields[0]->redacted);
        self::assertNull($entity->changes->fields[0]->new);
        self::assertSame('email', $entity->changes->fields[1]->field);
        self::assertSame('b@x', $entity->changes->fields[1]->new);

        self::assertSame('user.password_changed', $entity->render->message->translationKey);
        self::assertSame(['by' => 'self'], $entity->render->message->parameters);
        self::assertCount(1, $entity->render->info);
        self::assertSame('note', $entity->render->info[0]->translationKey);
    }
}
