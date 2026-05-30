<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Infrastructure\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;
use Override;
use WeDevelop\AuditLog\Event\RenderLine;
use WeDevelop\AuditLog\Event\RenderPayload;

use function is_string;
use function sprintf;

use const JSON_THROW_ON_ERROR;

final class RenderPayloadType extends Type
{
    public const string NAME = 'audit_render';

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
        if (!$value instanceof RenderPayload) {
            throw new InvalidArgumentException(sprintf('Expected %s, got %s.', RenderPayload::class, get_debug_type($value)));
        }

        return json_encode([
            'message' => $this->encodeLine($value->message),
            'info' => array_map($this->encodeLine(...), $value->info),
        ], JSON_THROW_ON_ERROR);
    }

    #[Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?RenderPayload
    {
        if (null === $value) {
            return null;
        }
        if (!is_string($value)) {
            throw new InvalidArgumentException(sprintf('Expected a JSON string, got %s.', get_debug_type($value)));
        }

        /** @var array{message: array{key: string, params: array<string, scalar>}, info: list<array{key: string, params: array<string, scalar>}>} $data */
        $data = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

        return new RenderPayload(
            new RenderLine($data['message']['key'], $data['message']['params']),
            array_map(static fn (array $l): RenderLine => new RenderLine($l['key'], $l['params']), $data['info']),
        );
    }

    /** @return array{key: string, params: array<string, scalar>} */
    private function encodeLine(RenderLine $line): array
    {
        return ['key' => $line->translationKey, 'params' => $line->parameters];
    }
}
