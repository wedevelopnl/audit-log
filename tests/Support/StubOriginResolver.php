<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Support;

use Override;
use WeDevelop\AuditLog\Recording\Origin;
use WeDevelop\AuditLog\Recording\OriginResolver;

final readonly class StubOriginResolver implements OriginResolver
{
    public function __construct(private Origin $origin)
    {
    }

    #[Override]
    public function resolve(): Origin
    {
        return $this->origin;
    }
}
