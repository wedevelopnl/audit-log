<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Infrastructure\Symfony\Uid;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;
use WeDevelop\AuditLog\Infrastructure\Symfony\Uid\UuidIdentityGenerator;

#[CoversClass(UuidIdentityGenerator::class)]
final class UuidIdentityGeneratorTest extends TestCase
{
    public function testIssuesTimeOrderedVersion7Uuids(): void
    {
        // The reader breaks ties on the id, which only follows append order
        // because v7 identifiers are time-ordered.
        $id = new UuidIdentityGenerator()->next();

        self::assertTrue(Uuid::isValid($id));
        self::assertInstanceOf(UuidV7::class, Uuid::fromString($id));
    }
}
