<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Fixtures\Event;

use Override;
use WeDevelop\AuditLog\Event\AbstractAuditEvent;

final readonly class DataCarryingEvent extends AbstractAuditEvent
{
    /** @param array<string, mixed> $data */
    public function __construct(private array $data)
    {
    }

    #[Override]
    public function code(): string
    {
        return 'invoice.paid';
    }

    /** @return array<string, mixed> */
    #[Override]
    public function data(): array
    {
        return $this->data;
    }
}
