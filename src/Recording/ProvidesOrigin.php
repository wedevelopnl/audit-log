<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Recording;

/**
 * Opt-in: the act carries its origin, captured where the act was decided (e.g.
 * the webhook request that dispatched the work to a Messenger worker, which the
 * OriginResolver would record as Console). Takes precedence over the
 * OriginResolver port, which is then not called.
 */
interface ProvidesOrigin
{
    public function origin(): Origin;
}
