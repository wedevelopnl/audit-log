<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Event;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WeDevelop\AuditLog\Event\JsonEncodable;
use WeDevelop\AuditLog\Event\RenderLine;

use const NAN;

#[CoversClass(RenderLine::class)]
#[CoversClass(JsonEncodable::class)]
final class RenderLineTest extends TestCase
{
    public function testKeepsEncodableParameters(): void
    {
        $line = new RenderLine('user.deleted', ['email' => 'jan@example.com', 'count' => 2]);

        self::assertSame(['email' => 'jan@example.com', 'count' => 2], $line->parameters);
    }

    public function testRejectsAParameterThatJsonCannotEncode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('RenderLine "invoice.paid" parameters must be JSON-encodable: Inf and NaN cannot be JSON encoded.');

        new RenderLine('invoice.paid', ['amount' => NAN]);
    }
}
