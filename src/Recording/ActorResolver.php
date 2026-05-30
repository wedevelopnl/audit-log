<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Recording;

/**
 * Resolves the acting principal from ambient state.
 */
interface ActorResolver
{
    public function resolve(): ?Actor;
}
