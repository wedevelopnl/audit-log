<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Infrastructure\Symfony\Uid;

use Override;
use Symfony\Component\Uid\Uuid;
use WeDevelop\AuditLog\Recording\IdentityGenerator;

/**
 * Time-ordered (UUID v7) identifiers: good index locality and a natural append
 * order for the trail.
 */
final readonly class UuidIdentityGenerator implements IdentityGenerator
{
    #[Override]
    public function next(): string
    {
        return Uuid::v7()->toRfc4122();
    }
}
