<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Recording;

interface IdentityGenerator
{
    public function next(): string;
}
