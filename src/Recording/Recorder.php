<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Recording;

use WeDevelop\AuditLog\Event\AuditEvent;

/**
 * The single write seam: captures the moment of an auditable act and appends a
 * frozen record.
 */
interface Recorder
{
    public function record(AuditEvent $event): void;
}
