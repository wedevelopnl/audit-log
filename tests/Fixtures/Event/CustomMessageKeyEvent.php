<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Fixtures\Event;

use Override;
use WeDevelop\AuditLog\Event\AbstractAuditEvent;

/**
 * Overrides messageKey() so the rendered translation key differs from the stable
 * action code: exercises the messageKey() seam.
 */
final readonly class CustomMessageKeyEvent extends AbstractAuditEvent
{
    #[Override]
    public function code(): string
    {
        return 'custom.code';
    }

    #[Override]
    protected function messageKey(): string
    {
        return 'custom.message_key';
    }
}
