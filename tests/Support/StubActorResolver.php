<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Support;

use Override;
use WeDevelop\AuditLog\Recording\Actor;
use WeDevelop\AuditLog\Recording\ActorResolver;

final readonly class StubActorResolver implements ActorResolver
{
    public function __construct(private ?Actor $actor)
    {
    }

    #[Override]
    public function resolve(): ?Actor
    {
        return $this->actor;
    }
}
