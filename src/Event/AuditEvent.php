<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Event;

/**
 * The domain's account of an auditable act. Pure: no clock, no identity, no
 * services. The recorder freezes its output at the moment of the act.
 */
interface AuditEvent
{
    public function code(): string;

    public function subject(): ?Subject;

    public function changes(): ?Changeset;

    /** @return array<string, mixed>|null */
    public function data(): ?array;

    public function render(): RenderPayload;
}
