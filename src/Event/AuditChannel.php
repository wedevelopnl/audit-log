<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Event;

enum AuditChannel: string
{
    case Ui = 'ui';
    case Api = 'api';
    case Console = 'console';
    case Job = 'job';
    case Webhook = 'webhook';
    case Unknown = 'unknown';
}
