<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Infrastructure\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;
use Override;

use function is_string;
use function sprintf;

use const JSON_THROW_ON_ERROR;

/**
 * A frozen value object stored as a JSON column. Subclasses only shape the
 * value to and from its JSON array.
 *
 * @template T of object
 */
abstract class JsonValueType extends Type
{
    /** @param array<string, mixed> $column */
    #[Override]
    final public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getJsonTypeDeclarationSQL($column);
    }

    #[Override]
    final public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        $class = $this->valueClass();
        if (!$value instanceof $class) {
            throw new InvalidArgumentException(sprintf('Expected %s, got %s.', $class, get_debug_type($value)));
        }

        return json_encode($this->toJson($value), JSON_THROW_ON_ERROR);
    }

    /** @return T|null */
    #[Override]
    final public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?object
    {
        if (null === $value) {
            return null;
        }
        if (!is_string($value)) {
            throw new InvalidArgumentException(sprintf('Expected a JSON string, got %s.', get_debug_type($value)));
        }

        /** @var array<array-key, mixed> $data */
        $data = json_decode($value, true, flags: JSON_THROW_ON_ERROR);

        return $this->fromJson($data);
    }

    /** @return class-string<T> */
    abstract protected function valueClass(): string;

    /**
     * @param T $value
     *
     * @return array<array-key, mixed>
     */
    abstract protected function toJson(object $value): array;

    /**
     * @param array<array-key, mixed> $data
     *
     * @return T
     */
    abstract protected function fromJson(array $data): object;
}
