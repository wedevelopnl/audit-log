<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Recording;

/**
 * Resolves the channel and network address of the act from the runtime
 * entrypoint.
 */
interface OriginResolver
{
    public function resolve(): Origin;
}
