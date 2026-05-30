<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Reading;

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
}
