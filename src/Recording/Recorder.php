<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Recording;

use WeDevelop\AuditLog\Event\AuditEvent;

/**
 * Appends a record of the event. The record is not written here: it commits with
 * the caller's next flush, atomically with the audited change (ADR 0010). Record
 * before that flush; a record made after the last one is lost.
 */
interface Recorder
{
    public function record(AuditEvent $event): void;
}
