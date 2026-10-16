<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Infrastructure\Doctrine\Type;

use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use WeDevelop\AuditLog\Event\Changeset;
use WeDevelop\AuditLog\Event\RenderPayload;
use WeDevelop\AuditLog\Infrastructure\Doctrine\Type\ChangesetType;
use WeDevelop\AuditLog\Infrastructure\Doctrine\Type\JsonValueType;
use WeDevelop\AuditLog\Infrastructure\Doctrine\Type\RenderPayloadType;

use function sprintf;

#[CoversClass(JsonValueType::class)]
#[CoversClass(ChangesetType::class)]
#[CoversClass(RenderPayloadType::class)]
final class JsonValueTypesTest extends TestCase
{
    /** @return iterable<string, array{Type}> */
    public static function types(): iterable
    {
        foreach (self::typesWithValueClass() as $name => [$type]) {
            yield $name => [$type];
        }
    }

    /** @return iterable<string, array{Type, class-string}> */
    public static function typesWithValueClass(): iterable
    {
        yield 'changeset' => [new ChangesetType(), Changeset::class];
        yield 'render payload' => [new RenderPayloadType(), RenderPayload::class];
    }

    #[DataProvider('types')]
    public function testDeclaresAJsonColumn(Type $type): void
    {
        $platform = new SQLitePlatform();

        self::assertSame($platform->getJsonTypeDeclarationSQL([]), $type->getSQLDeclaration([], $platform));
    }

    #[DataProvider('types')]
    public function testStoresNullAsNull(Type $type): void
    {
        self::assertNull($type->convertToDatabaseValue(null, new SQLitePlatform()));
    }

    #[DataProvider('types')]
    public function testReadsNullAsNull(Type $type): void
    {
        self::assertNull($type->convertToPHPValue(null, new SQLitePlatform()));
    }

    /** @param class-string $class */
    #[DataProvider('typesWithValueClass')]
    public function testRejectsAValueOfTheWrongClass(Type $type, string $class): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Expected %s, got stdClass.', $class));

        $type->convertToDatabaseValue(new stdClass(), new SQLitePlatform());
    }

    #[DataProvider('types')]
    public function testRejectsADatabaseValueThatIsNotAString(Type $type): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a JSON string, got int.');

        $type->convertToPHPValue(42, new SQLitePlatform());
    }
}
