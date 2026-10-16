<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Fixtures\Event;

use Override;
use WeDevelop\AuditLog\Event\AbstractAuditEvent;
use WeDevelop\AuditLog\Event\RenderLine;

/**
 * Assembles its info lines from two lists with `yield from`, so both sources
 * yield key 0: a key-preserving collection would keep only the last line.
 */
final readonly class MultiSourceInfoEvent extends AbstractAuditEvent
{
    #[Override]
    public function code(): string
    {
        return 'invoice.paid';
    }

    /** @return iterable<RenderLine> */
    #[Override]
    protected function additionalInfo(): iterable
    {
        yield from [new RenderLine('invoice.paid.amount')];
        yield from [new RenderLine('invoice.paid.method')];
    }
}
