<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Infrastructure\Doctrine\Type;

use Override;
use WeDevelop\AuditLog\Event\Changeset;
use WeDevelop\AuditLog\Event\FieldChange;

/** @extends JsonValueType<Changeset> */
final class ChangesetType extends JsonValueType
{
    public const string NAME = 'audit_changeset';

    #[Override]
    protected function valueClass(): string
    {
        return Changeset::class;
    }

    /** @return list<array{field: string, old: mixed, new: mixed, redacted: bool}> */
    #[Override]
    protected function toJson(object $value): array
    {
        return array_map(
            static fn (FieldChange $f): array => [
                'field' => $f->field,
                'old' => $f->old,
                'new' => $f->new,
                'redacted' => $f->redacted,
            ],
            $value->fields,
        );
    }

    #[Override]
    protected function fromJson(array $data): Changeset
    {
        /** @var list<array{field: string, old: mixed, new: mixed, redacted: bool}> $data */
        $fields = array_map(
            static fn (array $r): FieldChange => $r['redacted']
                ? FieldChange::redacted($r['field'])
                : FieldChange::of($r['field'], $r['old'], $r['new']),
            $data,
        );

        return new Changeset(...$fields);
    }
}
