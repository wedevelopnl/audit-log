<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Recording;

/**
 * Generates the identifier for a new record.
 */
interface IdentityGenerator
{
    public function next(): string;
}
