<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Event;

interface AuditEvent
{
    public function code(): string;

    public function subject(): ?Subject;

    public function changes(): ?Changeset;

    /** @return array<string, mixed>|null */
    public function data(): ?array;

    public function render(): RenderPayload;
}
