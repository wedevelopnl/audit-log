<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Recording;

use WeDevelop\AuditLog\Event\AuditEvent;

interface Recorder
{
    public function record(AuditEvent $event): void;
}
