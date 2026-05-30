<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WeDevelop\AuditLog\Event\Changeset;
use WeDevelop\AuditLog\Event\FieldChange;

#[CoversClass(Changeset::class)]
final class ChangesetTest extends TestCase
{
    public function testNormalizesFieldsToAZeroIndexedList(): void
    {
        // Named-argument unpacking would otherwise preserve string keys; the
        // changeset must reindex so downstream positional access (fields[0]) holds.
        $changeset = new Changeset(...['first' => FieldChange::of('role', 'member', 'admin')]);

        self::assertSame([0], array_keys($changeset->fields));
        self::assertSame('role', $changeset->fields[0]->field);
    }
}
