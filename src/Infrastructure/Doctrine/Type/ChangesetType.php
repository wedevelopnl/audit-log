<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Infrastructure\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;
use Override;
use WeDevelop\AuditLog\Event\Changeset;
use WeDevelop\AuditLog\Event\FieldChange;

use function is_string;
use function sprintf;

use const JSON_THROW_ON_ERROR;

final class ChangesetType extends Type
{
    public const string NAME = 'audit_changeset';

    /** @param array<string, mixed> $column */
    #[Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getJsonTypeDeclarationSQL($column);
    }

    #[Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }
        if (!$value instanceof Changeset) {
            throw new InvalidArgumentException(sprintf('Expected %s, got %s.', Changeset::class, get_debug_type($value)));
        }

        $rows = array_map(
            static fn (FieldChange $f): array => [
                'field' => $f->field,
                'old' => $f->old,
                'new' => $f->new,
                'redacted' => $f->redacted,
            ],
            $value->fields,
        );

        return json_encode($rows, JSON_THROW_ON_ERROR);
    }

    #[Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?Changeset
    {
        if (null === $value) {
            return null;
        }
        if (!is_string($value)) {
            throw new InvalidArgumentException(sprintf('Expected a JSON string, got %s.', get_debug_type($value)));
        }

        /** @var list<array{field: string, old: mixed, new: mixed, redacted: bool}> $rows */
        $rows = json_decode($value, true, flags: JSON_THROW_ON_ERROR);

        $fields = array_map(
            static fn (array $r): FieldChange => $r['redacted']
                ? FieldChange::redacted($r['field'])
                : FieldChange::of($r['field'], $r['old'], $r['new']),
            $rows,
        );

        return new Changeset(...$fields);
    }
}
