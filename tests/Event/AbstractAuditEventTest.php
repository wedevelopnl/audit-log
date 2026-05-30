<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WeDevelop\AuditLog\Event\AbstractAuditEvent;
use WeDevelop\AuditLog\Tests\Fixtures\Event\UserRoleChangedEvent;

#[CoversClass(AbstractAuditEvent::class)]
final class AbstractAuditEventTest extends TestCase
{
    public function testRenderAssemblesMessageFromCodeAndParameters(): void
    {
        $payload = new UserRoleChangedEvent('user-1', 'member', 'admin')->render();

        self::assertSame('user.role_changed', $payload->message->translationKey);
        self::assertSame(['from' => 'member', 'to' => 'admin'], $payload->message->parameters);
    }

    public function testRenderIncludesAdditionalInfoLines(): void
    {
        $payload = new UserRoleChangedEvent('user-1', 'member', 'admin')->render();

        self::assertCount(1, $payload->info);
        self::assertSame('user.role_changed.note', $payload->info[0]->translationKey);
        self::assertSame(['actor' => 'system'], $payload->info[0]->parameters);
    }

    public function testDataDefaultsToNull(): void
    {
        self::assertNull(new UserRoleChangedEvent('user-1', 'member', 'admin')->data());
    }
}
