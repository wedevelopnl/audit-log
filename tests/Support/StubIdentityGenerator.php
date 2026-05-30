<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Support;

use Override;
use WeDevelop\AuditLog\Recording\IdentityGenerator;

final readonly class StubIdentityGenerator implements IdentityGenerator
{
    public function __construct(private string $id)
    {
    }

    #[Override]
    public function next(): string
    {
        return $this->id;
    }
}
