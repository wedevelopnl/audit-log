<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Event;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use WeDevelop\AuditLog\Event\FieldChange;

#[CoversClass(FieldChange::class)]
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
}
