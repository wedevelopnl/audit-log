<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Event;

use function sprintf;

final readonly class RenderLine
{
    /** @param array<string, scalar> $parameters */
    public function __construct(
        public string $translationKey,
        public array $parameters = [],
    ) {
        JsonEncodable::assert($parameters, sprintf('RenderLine "%s" parameters', $translationKey));
    }
}
