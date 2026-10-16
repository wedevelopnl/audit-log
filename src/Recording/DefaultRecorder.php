<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Recording;

use Override;
use Psr\Clock\ClockInterface;
use WeDevelop\AuditLog\Event\AuditEvent;
use WeDevelop\AuditLog\Event\JsonEncodable;
use WeDevelop\AuditLog\Event\ProvidesSubjectLabel;

use function sprintf;

final readonly class DefaultRecorder implements Recorder
{
    public function __construct(
        private ClockInterface $clock,
        private ActorResolver $actors,
        private SubjectLabeller $labeller,
        private OriginResolver $origin,
        private RecordStore $store,
        private IdentityGenerator $ids,
    ) {
    }

    #[Override]
    public function record(AuditEvent $event): void
    {
        $data = $event->data();
        JsonEncodable::assert($data, sprintf('Audit event "%s" data', $event->code()));

        $subject = $event->subject();
        $label = $event instanceof ProvidesSubjectLabel
            ? $event->subjectLabel()
            : $this->labeller->label($subject);
        $actor = $this->actors->resolve();
        $origin = $this->origin->resolve();

        $this->store->add(new NewAuditRecord(
            id: $this->ids->next(),
            code: $event->code(),
            channel: $origin->channel,
            occurredAt: $this->clock->now(),
            actorId: $actor?->id,
            actorLabel: $actor?->label,
            subjectClass: $subject?->class,
            subjectId: $subject?->identifier,
            subjectLabel: $label,
            ipAddress: $origin->ipAddress,
            changes: $event->changes(),
            data: $data,
            render: $event->render(),
        ));
    }
}
