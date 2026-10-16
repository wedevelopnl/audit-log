<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Event;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use WeDevelop\AuditLog\Event\FieldChange;
use WeDevelop\AuditLog\Event\JsonEncodable;

use function sprintf;

use const INF;
use const NAN;

#[CoversClass(FieldChange::class)]
#[CoversClass(JsonEncodable::class)]
final class FieldChangeTest extends TestCase
{
    /** @return iterable<string, array{mixed, mixed}> */
    public static function jsonSafeValues(): iterable
    {
        yield 'strings' => ['member', 'admin'];
        yield 'ints' => [1, 2];
        yield 'floats' => [1.5, 2.5];
        yield 'bools' => [false, true];
        yield 'nulls' => [null, null];
    }

    #[DataProvider('jsonSafeValues')]
    public function testAcceptsScalarOrNullValues(mixed $old, mixed $new): void
    {
        $change = FieldChange::of('role', $old, $new);

        self::assertSame($old, $change->old);
        self::assertSame($new, $change->new);
    }

    public function testRejectsANonScalarOldValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('FieldChange "roles" old value must be a scalar or null; got array.');

        FieldChange::of('roles', ['admin'], 'admin');
    }

    public function testRejectsANonScalarNewValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('FieldChange "at" new value must be a scalar or null; got DateTimeImmutable.');

        FieldChange::of('at', null, new DateTimeImmutable('2026-05-30T00:00:00+00:00'));
    }

    /** @return iterable<string, array{float|string, string}> */
    public static function scalarsJsonCannotEncode(): iterable
    {
        yield 'NAN' => [NAN, 'Inf and NaN cannot be JSON encoded'];
        yield 'INF' => [INF, 'Inf and NaN cannot be JSON encoded'];
        yield 'negative INF' => [-INF, 'Inf and NaN cannot be JSON encoded'];
        yield 'invalid UTF-8' => ["\xB1\x31", 'Malformed UTF-8 characters, possibly incorrectly encoded'];
    }

    #[DataProvider('scalarsJsonCannotEncode')]
    public function testRejectsAnOldScalarThatJsonCannotEncode(float|string $value, string $reason): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('FieldChange "score" old value must be JSON-encodable: %s.', $reason));

        FieldChange::of('score', $value, 1);
    }

    #[DataProvider('scalarsJsonCannotEncode')]
    public function testRejectsANewScalarThatJsonCannotEncode(float|string $value, string $reason): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('FieldChange "score" new value must be JSON-encodable: %s.', $reason));

        FieldChange::of('score', 1, $value);
    }
}
