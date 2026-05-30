<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Reading;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WeDevelop\AuditLog\Reading\AuditQuery;

#[CoversClass(AuditQuery::class)]
final class AuditQueryTest extends TestCase
{
    public function testDefaultsToTheFirstPageWithFiftyPerPage(): void
    {
        $query = new AuditQuery();

        self::assertSame(1, $query->page);
        self::assertSame(50, $query->perPage);
    }

    public function testAcceptsTheMinimumValidBounds(): void
    {
        $query = new AuditQuery(page: 1, perPage: 1);

        self::assertSame(1, $query->page);
        self::assertSame(1, $query->perPage);
    }

    public function testRejectsPageBelowOne(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new AuditQuery(page: 0);
    }

    public function testRejectsPerPageBelowOne(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new AuditQuery(perPage: 0);
    }
}
