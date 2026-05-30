<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Event;

use Override;

/**
 * Base for concrete audit events. A subclass must implement code(); everything
 * else has a safe default. Override parameters()/additionalInfo() to shape the
 * frozen render payload, and subject()/changes()/data() to add detail.
 */
abstract readonly class AbstractAuditEvent implements AuditEvent
{
    #[Override]
    public function subject(): ?Subject
    {
        return null;
    }

    #[Override]
    public function changes(): ?Changeset
    {
        return null;
    }

    /** @return array<string, mixed>|null */
    #[Override]
    public function data(): ?array
    {
        return null;
    }

    #[Override]
    public function render(): RenderPayload
    {
        $info = [];
        foreach ($this->additionalInfo() as $line) {
            $info[] = $line;
        }

        return new RenderPayload(new RenderLine($this->messageKey(), $this->parameters()), $info);
    }

    protected function messageKey(): string
    {
        return $this->code();
    }

    /** @return array<string, scalar> */
    protected function parameters(): array
    {
        return [];
    }

    /** @return iterable<RenderLine> */
    protected function additionalInfo(): iterable
    {
        return [];
    }
}
