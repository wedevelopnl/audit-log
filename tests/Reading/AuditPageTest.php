<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Reading;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WeDevelop\AuditLog\Reading\AuditPage;

#[CoversClass(AuditPage::class)]
final class AuditPageTest extends TestCase
{
    public function testPageCountRoundsUp(): void
    {
        self::assertSame(3, new AuditPage([], page: 2, perPage: 20, total: 45)->pageCount);
    }

    public function testPageCountIsAtLeastOneWhenEmpty(): void
    {
        self::assertSame(1, new AuditPage([], page: 1, perPage: 20, total: 0)->pageCount);
    }

    public function testAllowsPerPageOfOne(): void
    {
        self::assertSame(5, new AuditPage([], page: 1, perPage: 1, total: 5)->pageCount);
    }

    public function testRejectsPerPageBelowOne(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new AuditPage([], page: 1, perPage: 0, total: 5);
    }
}
