<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Recording;

/**
 * Resolves the acting principal from ambient state. Implementations MUST read
 * the principal live, at call time (worker mode); they capture no state.
 */
interface ActorResolver
{
    public function resolve(): ?Actor;
}
