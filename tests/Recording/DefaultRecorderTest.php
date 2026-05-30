<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Recording;

use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;
use WeDevelop\AuditLog\Event\AuditChannel;
use WeDevelop\AuditLog\Event\Changeset;
use WeDevelop\AuditLog\Event\FieldChange;
use WeDevelop\AuditLog\Event\RenderLine;
use WeDevelop\AuditLog\Event\RenderPayload;
use WeDevelop\AuditLog\Event\Subject;
use WeDevelop\AuditLog\Recording\Actor;
use WeDevelop\AuditLog\Recording\DefaultRecorder;
use WeDevelop\AuditLog\Recording\NewAuditRecord;
use WeDevelop\AuditLog\Recording\Origin;
use WeDevelop\AuditLog\Tests\Fixtures\Event\UserDeletedEvent;
use WeDevelop\AuditLog\Tests\Fixtures\Event\UserPasswordChangedEvent;
use WeDevelop\AuditLog\Tests\Fixtures\Event\UserRoleChangedEvent;
use WeDevelop\AuditLog\Tests\Support\FixedClock;
use WeDevelop\AuditLog\Tests\Support\InMemoryRecordStore;
use WeDevelop\AuditLog\Tests\Support\StubActorResolver;
use WeDevelop\AuditLog\Tests\Support\StubIdentityGenerator;
use WeDevelop\AuditLog\Tests\Support\StubOriginResolver;
use WeDevelop\AuditLog\Tests\Support\StubSubjectLabeller;

#[CoversClass(DefaultRecorder::class)]
#[CoversClass(NewAuditRecord::class)]
#[CoversClass(Actor::class)]
#[CoversClass(Origin::class)]
#[CoversClass(Subject::class)]
#[CoversClass(Changeset::class)]
#[CoversClass(FieldChange::class)]
#[CoversClass(RenderPayload::class)]
#[CoversClass(RenderLine::class)]
final class DefaultRecorderTest extends TestCase
{
    public function testFreezesEveryStrandOfTheMomentIntoTheStoredRecord(): void
    {
        $store = new InMemoryRecordStore();
        $this->recorder($store, new Actor('actor-1', 'Jan'), 'the-label', new Origin(AuditChannel::Api, '203.0.113.7'), 'rec-1')
            ->record(new UserRoleChangedEvent('user-1', 'member', 'admin'));

        $record = $store->records[0];
        self::assertSame('rec-1', $record->id);
        self::assertSame('user.role_changed', $record->code);
        self::assertSame(AuditChannel::Api, $record->channel);
        self::assertSame('2026-05-30T10:00:00+00:00', $record->occurredAt->format(DateTimeInterface::ATOM));
        self::assertSame('actor-1', $record->actorId);
        self::assertSame('Jan', $record->actorLabel);
        self::assertSame(stdClass::class, $record->subjectClass);
        self::assertSame('user-1', $record->subjectId);
        self::assertSame('the-label', $record->subjectLabel);
        self::assertSame('203.0.113.7', $record->ipAddress);
        self::assertNotNull($record->changes);
        self::assertSame('role', $record->changes->fields[0]->field);
        self::assertSame('admin', $record->changes->fields[0]->new);
        self::assertSame('user.role_changed', $record->render->message->translationKey);
    }

    public function testEventProvidedSubjectLabelShortCircuitsTheLabeller(): void
    {
        $store = new InMemoryRecordStore();
        $this->recorder($store, null, 'SHOULD-NOT-BE-USED', new Origin(AuditChannel::Ui, null), 'rec-2')
            ->record(new UserDeletedEvent('user-1', 'jan@pouw.nl'));

        self::assertSame('jan@pouw.nl', $store->records[0]->subjectLabel);
        self::assertNull($store->records[0]->actorId);
        self::assertNull($store->records[0]->actorLabel);
    }

    public function testRedactedChangeWithholdsValuesInTheStoredRecord(): void
    {
        $store = new InMemoryRecordStore();
        $this->recorder($store, new Actor('actor-1', 'Jan'), null, new Origin(AuditChannel::Ui, null), 'rec-3')
            ->record(new UserPasswordChangedEvent('user-1'));

        $field = $store->records[0]->changes?->fields[0];
        self::assertNotNull($field);
        self::assertSame('password', $field->field);
        self::assertTrue($field->redacted);
        self::assertNull($field->old);
        self::assertNull($field->new);
    }

    private function recorder(
        InMemoryRecordStore $store,
        ?Actor $actor,
        ?string $label,
        Origin $origin,
        string $id,
    ): DefaultRecorder {
        return new DefaultRecorder(
            new FixedClock(new DateTimeImmutable('2026-05-30T10:00:00+00:00')),
            new StubActorResolver($actor),
            new StubSubjectLabeller($label),
            new StubOriginResolver($origin),
            $store,
            new StubIdentityGenerator($id),
        );
    }
}
