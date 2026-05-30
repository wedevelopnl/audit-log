<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Infrastructure\Symfony\Http;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use WeDevelop\AuditLog\Event\AuditChannel;
use WeDevelop\AuditLog\Infrastructure\Symfony\Http\RequestOriginResolver;

#[CoversClass(RequestOriginResolver::class)]
final class RequestOriginResolverTest extends TestCase
{
    public function testReadsTheCurrentRequestAtCallTime(): void
    {
        $stack = new RequestStack();
        $resolver = new RequestOriginResolver($stack);

        // No request (e.g. CLI/worker boot) — console channel.
        self::assertSame(AuditChannel::Console, $resolver->resolve()->channel);

        $stack->push(Request::create('/x', server: ['REMOTE_ADDR' => '203.0.113.7']));
        $origin = $resolver->resolve();
        self::assertSame(AuditChannel::Ui, $origin->channel);
        self::assertSame('203.0.113.7', $origin->ipAddress);

        // Request finished; resolver reflects the change.
        $stack->pop();
        self::assertSame(AuditChannel::Console, $resolver->resolve()->channel);
    }
}
