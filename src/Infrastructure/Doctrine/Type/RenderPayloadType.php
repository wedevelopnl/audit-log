<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Infrastructure\Doctrine\Type;

use Override;
use WeDevelop\AuditLog\Event\RenderLine;
use WeDevelop\AuditLog\Event\RenderPayload;

/** @extends JsonValueType<RenderPayload> */
final class RenderPayloadType extends JsonValueType
{
    public const string NAME = 'audit_render';

    #[Override]
    protected function valueClass(): string
    {
        return RenderPayload::class;
    }

    /** @return array{message: array{key: string, params: array<string, scalar>}, info: list<array{key: string, params: array<string, scalar>}>} */
    #[Override]
    protected function toJson(object $value): array
    {
        return [
            'message' => $this->encodeLine($value->message),
            'info' => array_map($this->encodeLine(...), $value->info),
        ];
    }

    #[Override]
    protected function fromJson(array $data): RenderPayload
    {
        /** @var array{message: array{key: string, params: array<string, scalar>}, info: list<array{key: string, params: array<string, scalar>}>} $data */
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
